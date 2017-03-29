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

namespace Peraleks\LaravelPrettyErrors\Trace;


use Peraleks\LaravelPrettyErrors\Core\ConfigObject;

/**
 * Interface FormatterInterface
 *
 * Все форматировщики стека вызовов должны реализовывать
 * данный интерфейс.<br>
 * Так же форматировщик может расширять AbstractTraceFormatter,
 * который реализует данный интерфейс и выполняет часть работы
 * по форматированию стека.
 */
interface FormatterInterface
{
    /**
     * Возвращает форматированный стек вызовов.
     *
     * @param array        $dBTrace      стек вызовов
     * @param ConfigObject $configObject объект конфигурации
     * @return string
     */
    function getFormattedTrace(array $dBTrace, ConfigObject $configObject): string;
}