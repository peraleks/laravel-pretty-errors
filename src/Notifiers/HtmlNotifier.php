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

use Peraleks\LaravelPrettyErrors\Trace\HtmlTraceFormatter;

/**
 * Class HtmlNotifier
 *
 * Форматирует и выводит ошибку в браузер ввиде HTML.
 */
class HtmlNotifier extends AbstractNotifier
{
    /**
     * Полное имя файла стилей для html-шаблона ошибки.
     *
     * @var string
     */
    protected $errorCss;

    /**
     * Полное имя файла стилей для стека вызовов.
     *
     * @var string
     */
    protected $traceCss;

    /**
     * Полное имя файла html-шаблона ошибки.
     *
     * @var string
     */
    protected $errorTpl;

    /**
     * Полное имя файла html-шаблона обёртки для отложенного показа ошибок.
     *
     * @var string
     */
    protected $wrapperTpl;

    /**
     * Счётчик callbacks для отложенного показа ошибок.
     * Используется для того, чтобы не регистрировать callback повторно.
     *
     * @var null|int
     */
    protected static $count;

    /**
     * Задаёт файлы шаблонов и css.
     *
     * @return void
     */
    protected function before()
    {
        $dir = dirname(__DIR__).'/View';
        $this->errorCss   = $dir.'/error.css';
        $this->traceCss   = $dir.'/trace.css';
        $this->errorTpl   = $dir.'/error.tpl.php';
        $this->wrapperTpl = $dir.'/wrapper.tpl.php';
    }

    /**
     * Возвращает имя класса обработчика стека вызовов.
     *
     * @return string HtmlTraceHandler::class
     */
    protected function traceFormatterClass(): string
    {
        return HtmlTraceFormatter::class;
    }

    /**
     * Возвращает форматированную ошибку ввиде HTML.
     *
     * @param string $trace стек вызовов
     * @return string ошибка в формате HTML
     */
    protected function ErrorToString(string $trace): string
    {
        $eObj = $this->errorObject;
        $conf = $this->configObject;

        $code     = $eObj->getCode();
        $type     = $eObj->getType();
        $message  = $eObj->getMessage();
        $path     = $conf->getAppDir();
        $file     = preg_replace('#^'.$path.'#', '', $eObj->getFile());
        $line     = $eObj->getLine();
        $fontSize = $conf->get('fontSize');

        if (E_ERROR === $code) $cssType = 'ERROR';
        elseif (E_PARSE === $code) $cssType = 'ParseError';
        else $cssType = $type;

        $conf->get('hideTrace') ? $hidden = 'hidden' : $hidden = '';
        $style = file_get_contents($this->errorCss);
        $trace == '' ?: $style .= file_get_contents($this->traceCss);
        $traceCount = count($eObj->getTrace());

        ob_start();
        include($this->errorTpl);
        return ob_get_clean();
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
