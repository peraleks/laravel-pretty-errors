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

namespace Peraleks\LaravelPrettyErrors\Trace;

/**
 * Class BrowserConsoleTraceFormatter
 *
 * Форматирует стек вызовов для отображения в консоли браузера.
 */
class BrowserConsoleTraceFormatter extends AbstractTraceFormatter
{
    /**
     * Ничего не делает.
     */
    protected function before() {}

    /**
     * Здесь производим окончательное форматирование массива стека вызовов
     * и формируем конечную строку.
     *
     * @param array $traceArray предварительно отформатированный стек
     * @return string окончательный результат обработки стека
     */
    protected function completion(array $traceArray): string
    {
        $path = $this->configObject->getAppDir();
        $trace = '';
        for ($i = 0, $c = count($traceArray); $i < $c; ++$i) {
            $v =& $traceArray[$i];
            $file = preg_replace('#^'.$path.'#', '', $v['file']);

            $trace .= '#'.$i.' '.$file
                .('0' === $v['line'] ? '[internal function]: ' : ' ( '.$v['line'].' ) ')
                .$v['class'].$v['function'].'\n';
        }
        return $trace;
    }
}