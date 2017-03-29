<?php
/**
 * Error formatter for Laravel.
 *
 * @package   Peraleks\LaravelPrettyErrors
 * @copyright 2017 Aleksey Perevoshchikov <aleksey.perevoshchikov.n@gmail.com>
 * @license   https://github.com/peraleks/laravel-pretty-errors/blob/master/LICENSE.md MIT
 * @link      https://github.com/peraleks/laravel-pretty-errors
 *
 */

declare(strict_types=1);

namespace Peraleks\LaravelPrettyErrors\Core;
use Peraleks\LaravelPrettyErrors\Notifiers\ProductionNotifier;

/**
 * Class SelfErrorHandler
 *
 * Обработчик внутренних ошибок.
 * Реализует логирование и отображение внутренних ошибок и
 * неудачно обработанных ошибок клиентской части кода. Так же посылает
 * код состояния 500 в случае фатальной ошибки.
 */
class SelfErrorHandler
{
    /**
     * Соответствие кодов ошибок их названиям.
     *
     * @var array
     */
    private  $codeName = [
        E_ERROR             => 'ERROR',
        E_WARNING           => 'WARNING',
        E_PARSE             => 'PARSE',
        E_NOTICE            => 'NOTICE',
        E_CORE_ERROR        => 'CORE_ERROR',
        E_CORE_WARNING      => 'CORE_WARNING',
        E_COMPILE_ERROR     => 'COMPILE_ERROR',
        E_COMPILE_WARNING   => 'COMPILE_WARNING',
        E_USER_ERROR        => 'USER_ERROR',
        E_USER_WARNING      => 'USER_WARNING',
        E_USER_NOTICE       => 'USER_NOTICE',
        E_STRICT            => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED        => 'DEPRECATED',
        E_USER_DEPRECATED   => 'USER_DEPRECATED',
    ];

    /**
     * Полное имя файла лога внутренних ошибок.
     *
     * @var string
     */
    private $selfLogFile;

    /**
     * Флаг development режима.
     *
     * @var bool
     */
    private $devMode;

    /**
     * Маска ошибок, для которых надо показать стек вызовов.
     *
     * @var int
     */
    private $traceEnabled = E_ERROR | E_RECOVERABLE_ERROR;

    private $prodErrorCount;

    private $timeFormat = 'o-m-d H:i:s';

    /**
     * SelfErrorHandler constructor.
     *
     * Валидирует имя файла собственного лога ошибок.
     * И определяет dev | prod режимы.
     *
     * @param ConfigObject|null $configObject объект конфигурации
     */
    public function __construct(ConfigObject $configObject = null)
    {
        if ($configObject) {
            $this->selfLogFile = $configObject->getSelfLogFile();
            $this->devMode = ('dev' === $configObject->getMode());

            $configObject->setNotifierClass(ProductionNotifier::class);
            if (is_string($t = $configObject->get('timeFormat'))) $this->timeFormat = $t;

        } else {
            $this->selfLogFile = storage_path().'/logs/laravel.log';
            $this->devMode = config('app.debug');
        }
    }

    /**
     * Запускает обработку ошибки.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return void
     */
    public function format(ErrorObject $e): string
    {
        if ($this->devMode) {
            return $this->devReport($e);
        } else {
            return $this->prodReport($e, $this->selfLogFile);
        }
    }

    /**
     * Выводит сообщение ошибки в CLI режиме.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     */
    private function cliReport($e): string
    {
        return "\n\033[32m".$this->getStringError($e)."\033[0m\n";
    }

    /**
     * Выводит сообщение ошибки в браузер.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     */
    private function devReport($e)
    {
        $type    = $e->getType();
        $file    = $e->getFile();
        $line    = $e->getLine();
        $message = $e->getMessage();
        $trace   = $e->getCode() & $this->traceEnabled ? '<pre>'.$e->getTraceAsString().'</pre>' : '';

        ob_start();
        include dirname(__DIR__).'/View/selfError.tpl.php';
        return ob_get_clean();
    }

    /**
     * Пишет ошибку в файл.
     *
     * Eсли требуется, отправляет состояние 500 с последующим
     * прерыванием выполнения скрипта.
     *
     * @param \Throwable|ErrorObject $e    объект ошибки
     * @param string                 $file полное имя вайла лога внутренних ошибок
     */
    private function prodReport($e, string $file): string
    {
        if ($this->prodErrorCount) {
            if ($r = fopen($file, 'ab')) {
                fwrite($r, "\n[".date($this->timeFormat).'] '.$this->getStringError($e)."\n");
                fclose($r);
            }
        }

        if (!$this->prodErrorCount) {

            ++$this->prodErrorCount;

            headers_sent() ?: header('HTTP/1.1 500 Internal Server Error');

            ob_start();
            include dirname(__DIR__).'/View/serverError500.php';
            return ob_get_clean();
        } else {
            return '';
        }
    }

    /**
     * Возвращает конечную строку ошибки
     * со стеком вызовов или без.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return string
     */
    private function getStringError($e): string
    {
        if (!($e->getCode() & $this->traceEnabled)) {
            return $e->getType().': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine();
        }
        return (string)$e;
    }

}