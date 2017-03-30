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

/**
 * Class SelfErrorHandler
 *
 * Обработчик внутренних ошибок.
 * Реализует логирование и отображение внутренних ошибок.
 */
class SelfErrorHandler
{
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

    /**
     * SelfErrorHandler constructor.
     *
     * Определяет dev | prod режимы.
     *
     * @param ConfigObject|null $configObject объект конфигурации
     */
    public function __construct(ConfigObject $configObject = null)
    {
        $this->devMode = config('app.debug');
    }

    /**
     * Запускает обработку ошибки.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return string
     */
    public function format(ErrorObject $e): string
    {
        if ($this->devMode) {
            return $this->devPage($e);
        } else {
            return $this->prodPage($e);
        }
    }

    /**
     * Возвращает html-страницу с сообщением об ошибке для development режима.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     * @return string
     */
    private function devPage($e): string
    {
        if ($e instanceof InnerErrorObject) {
            SelfErrorLogger::log($e);
        }
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
     * Возвращает html-страницу с сообщением об ошибке для production режима.
     *
     * @param ErrorObject $e объект ошибки
     * @return string
     */
    private function prodPage($e): string
    {
        if ($e instanceof InnerErrorObject) {
            SelfErrorLogger::log($e);
        }
        ob_start();
        include dirname(__DIR__).'/View/page500.php';
        return ob_get_clean();
    }
}