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

use Peraleks\LaravelPrettyErrors\Trace\BrowserConsoleTraceFormatter;

/**
 * Class BrowserConsoleNotifier
 *
 * Форматирует и выводит ошибку в консоль браузера.
 */
class BrowserConsoleNotifier extends AbstractNotifier
{
    const ERROR      = "#e02828";
    const WARNING    = "#ffaa00";
    const NOTICE     = "#d8d800";
    const PARSE      = "#ba59bf";
    const DEPRECATED = "#c48c00";

    const SCRIPT = '<script>%s</script>';

    const HEADER = "console.%s('%s %s',"
                    ."'background: %s;"
                    ."color: #fff;"
                    ."padding: 0.3em 0.7em 0.3em 0.2em;"
                    ."line-height: 1.5em;"
                    ."border-radius: 1em');";

    const MESSAGE = "console.%s('%s');";

    const FILE = "console.%s('%s %s', 'color: #00aaaa; padding-left: 1em');";

    const END = "console.%s('%s %s',"
                    ."'background: %s;"
                    ."color: #fff;"
                    ."padding: 0.2em 0.5em 0.2em 0;"
                    ."line-height: 1.2em;"
                    ."border-radius: 1em');";

    /**
     * Соответствие кодов ошибок и цвета.
     *
     * @var array
     */
    protected $codeColor;

    /**
     * Счётчик callbacks для отложенного показа ошибок.
     * Используется для того, чтобы не регистрировать callback повторно.
     *
     * @var null|int
     */
    protected static $count;

    /**
     * Категория в консоли браузера.
     *
     * @var string
     */
    protected $console = 'log';

    /**
     * Инициализирует массив соответствия кодов ошибок и цвета.
     * Валидирует параметр конфигурации - 'console'.
     */
    protected function before()
    {
        $this->codeColor = [
            E_ERROR             => static::ERROR,
            E_CORE_ERROR        => static::ERROR,
            E_COMPILE_ERROR     => static::ERROR,
            E_USER_ERROR        => static::ERROR,
            E_RECOVERABLE_ERROR => static::ERROR,

            E_WARNING         => static::WARNING,
            E_CORE_WARNING    => static::WARNING,
            E_COMPILE_WARNING => static::WARNING,
            E_USER_WARNING    => static::WARNING,

            E_PARSE => static::PARSE,

            E_NOTICE      => static::NOTICE,
            E_USER_NOTICE => static::NOTICE,

            E_STRICT          => static::DEPRECATED,
            E_DEPRECATED      => static::DEPRECATED,
            E_USER_DEPRECATED => static::DEPRECATED,
        ];

        /* определяем в какую категорию консоли отправлять ошибки */
        if ($v = $this->configObject->get('console')) {
            !preg_match('/^error$|^warn$|^info$|^log$|^debug$/', $v, $matches)
                ?: $this->console = $matches[0];
        }
    }

    /**
     * Возвращает имя класса обработчика стека вызовов.
     *
     * @return string BrowserConsoleTraceHandler::class
     */
    protected function traceFormatterClass(): string
    {
        return BrowserConsoleTraceFormatter::class;
    }

    /**
     * Возвращает форматированную ошибку для вывода в консоль браузера.
     *
     * @param string $trace стек вызовов
     * @return string
     */
    protected function ErrorToString(string $trace): string
    {
        $eObj  = $this->errorObject;
        $color =& $this->codeColor;
        $cons  =& $this->console;

        $code     = $eObj->getCode();
        $type     = $eObj->getType();
        $message  = $eObj->getMessage();
        $file     = $eObj->getFile().' ( '.$eObj->getLine().' )';

        if ('' != $trace && $this->configObject->get('phpNativeTrace'))  {
            $trace = addslashes($trace);
            $trace = preg_replace("/\n/", '\n', $trace);
        }

        $string = sprintf(static::HEADER, $cons, '%c', $type.' ['.$code.']', $color[$code]);

        $string .= sprintf(static::MESSAGE, $cons, addslashes($message));

        $string .= sprintf(static::FILE, $cons, '%c', $file, $color[$code]);

        if ('' !== $trace) {
            if (!$this->configObject->get('phpNativeTrace')) {
                $appDir = $this->configObject->getAppDir();
                $fullFile = $eObj->getFile();
                $file = preg_replace('#^'.$appDir.'#', '', $fullFile);
                $string .= $fullFile === $file ? '' : sprintf(static::MESSAGE, $cons, '('.$appDir.')');
            }
            $string .= sprintf(static::MESSAGE, $cons, $trace);
        }

        $string .= sprintf(static::END, $cons, '%c', '^', $color[$code]);

        return sprintf(static::SCRIPT, $string);
    }


    /**
     * В зависимости от параметра 'deferredView' выводит сразу
     * ошибку в браузер, или регистрирует callback для отложенного
     * вывода.
     *
     * @param string $error форматированная ошибка
     * @return string
     */
    protected function notify(string $error): string
    {
        return $error;

    }
}