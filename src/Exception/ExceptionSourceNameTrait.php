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

namespace Peraleks\LaravelPrettyErrors\Exception;

/**
 * Class ExceptionSourceNameTrait
 *
 * Предоставляет метод exceptionSourceName().
 */
trait ExceptionSourceNameTrait
{
    /**
     * Возвращает название уведомителя полученное из имени файла.
     *
     * @return string название уведомителя
     */
    public function exceptionSourceName(): string
    {
        $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'];
        preg_match('/^.*\/(.+)\..+$/', $file, $arr);
        return (string)$arr[1];
    }
}
