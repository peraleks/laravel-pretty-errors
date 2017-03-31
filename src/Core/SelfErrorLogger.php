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


class SelfErrorLogger
{
    /**
     * Логирует ошибку.
     *
     * Передаёт в Illuminate\Foundation\Exceptions\ExceptionHandler::report()
     *
     * @param ErrorObject $e объект ошибки
     */
    public static function log(ErrorObject $e)
    {
        $handler = resolve('Illuminate\Contracts\Debug\ExceptionHandler');
        $handler->report($e->getErrorException());
    }
}