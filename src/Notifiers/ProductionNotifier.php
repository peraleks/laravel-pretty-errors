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

namespace Peraleks\LaravelPrettyErrors\Notifiers;

use Peraleks\LaravelPrettyErrors\Core\InnerErrorObject;
use Peraleks\LaravelPrettyErrors\Core\SelfErrorLogger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductionNotifier
 *
 * Подклучает пользовательский шаблон
 * или файл возвращающий html-страницу ошибки 404 | 500.
 * Если при подключении произошла ошибка, возвращается
 * страница по умолчпнию.
 */
class ProductionNotifier extends AbstractNotifier
{
    /**
     * Полное имя файла шаблона страницы 404 или страницы ошибки сервера (500),
     * который будет подключен, если пользовательский файл не определён
     * или в нём произошла ошибка.
     *
     * @var string
     */
    protected $defaultFile;

    /**
     * Файл, который будет подключен.
     *
     * Файл должен выводить результат в буфер вывода,
     * или возвращать строку. Также может быть простым PHP-шаблоном.
     *
     * Например файл может содержать такой код:
     *
     * return '<h2>Page not found</h2>;
     *
     * или
     *
     * echo view('404')->render();
     *
     *
     * Если хотите подключить шаблон blade, просто задайте в файле настроек (pretty-errors.php):
     *
     * 'file404' => 'view.404'
     *
     * где '404' имя шаблона (404.blade.php).
     *
     * @var string
     */
    protected $file;

    /**
     * Содержит страницу ошибки по умолчанию, или null.
     *
     * Если не null значит произошла внутренняя ошибка.
     *
     * @var null|string
     */
    protected $defaultPage;

    /**
     * Имя blade-шаблона.
     *
     * @var string
     */
    protected $view;

    /**
     * Определяет и валидирует имя файла,
     * выводящего/возвращающего html-страницу ошибки ('500' или '404').
     */
    protected function before()
    {
        if ((NotFoundHttpException::class === $this->errorObject->getType())
            && (0 === strpos($this->errorObject->getMessage(), '404'))
        ) {
            $this->defaultFile = dirname(__DIR__).'/View/page404.php';
            $this->file = $this->validateIncludeFile(
                $this->configObject->get('file404'),
                $this->defaultFile
            );
        } else {
            $this->defaultFile = dirname(__DIR__).'/View/page500.php';
            $this->file = $this->validateIncludeFile(
                $this->configObject->get('file500'),
                $this->defaultFile
            );
        }
    }

    /**
     * Возвращает пустую строку - стек обрабатываться не будет.
     *
     * @return string
     */
    protected function traceFormatterClass(): string
    {
        return '';
    }

    /**
     * Возвращает валидное имя файла для включения.
     *
     * @param  string $file    имя файла из конфигурации
     * @param  string $default имя файла по умолчанию
     * @return string
     */
    protected function validateIncludeFile($file, string $default): string
    {
        if ('' === $file || !is_string($file)) {
            return $default;
        }

        if (0 !== preg_match('/^(view.)(.+)$/', $file, $m)) {
            $this->view = $m[2];
            return '';
        }

        if (!file_exists($file)) {
            $this->sendToLog(
                new \Exception(
                    'ProductionNotifier settings error: file '.$file.' not exist'
                ));
            return $default;
        }
        return $file;
    }

    /**
     * Возвращает страницу ошибки.
     *
     * Подключает файл, выводящий/возвращающий страницу ошибки.
     * Если при подключении  происходит ошибка, отсылает её в лог.
     *
     * @param string $trace пустая строка
     * @return string
     */
    protected function ErrorToString(string $trace): string
    {
        ob_start();
        try {
            set_error_handler([$this, 'error']);

            if ($this->view) {
                $result = view($this->view)->render();
            } else {
                $result = include $this->file;
            }

        } catch (\Throwable $e) {

            $this->sendToLog($e);
            include $this->defaultFile;

        } finally {

            restore_error_handler();

            if (isset($result) && is_string($result)) {
                ob_end_clean();
                return $result;
            }
            return ob_get_clean();
        }
    }

    /**
     * Возвращает html-страницу ошибки.
     *
     * @param string $page страница ошибки
     * @return string
     */
    protected function notify(string $page): string
    {
        /* Если произошла ошибка при подключении, возвращаем
         * страницу по умолчанию */
        if ($this->defaultPage) return $this->defaultPage;

        return $page;
    }

    /**
     * Обрабатывает внутренние ошибки сгенерированные при подключении
     * файла в ErrorToString().
     *
     * Конвертирует ошибку в исключение. Генерирует страницу ошибки по умолчанию.
     *
     * @param int    $code    код ошибки
     * @param string $message сообщение ошибки
     * @param string $file    полное имя файла ошибки
     * @param int    $line    номер строки
     * @return bool true
     */
    public function error($code, $message, $file, $line)
    {
        $this->sendToLog(new \ErrorException($message, $code, $code, $file, $line));

        if (!$this->defaultPage) {
            ob_start();
            include $this->defaultFile;
            $this->defaultPage = ob_get_contents();
        }

        return true;
    }

    /**
     * Отсылает исключения в логгер внутренних ошибок.
     *
     * @param \Throwable $e
     */
    protected function sendToLog(\Throwable $e)
    {
        SelfErrorLogger::log(new InnerErrorObject($e));

    }

}