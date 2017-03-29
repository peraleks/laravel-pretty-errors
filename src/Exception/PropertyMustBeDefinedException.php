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
 * Class PropertyMustBeDefinedException
 *
 * Используется в случае если параметр конфигурации
 * не задан пользователем, но обязательно должен присутствовать.
 */
class PropertyMustBeDefinedException extends ErrorHandlerException
{
    use ExceptionSourceNameTrait;

    /**
     * PropertyMustBeDefinedException constructor.
     *
     * Форматирует сообщение исключения по шаблону:
     * "{имя уведомителя}: the property '{$property}'=> must be defined".
     * <br>
     * Например: "TailNotifier: the property 'file'=> must be defined".
     *
     * @param string $key ключ нассива конфигурации
     */
    public function __construct(string $key)
    {
        $this->message = $this->exceptionSourceName().': the property \''.$key.'\'=> must be defined';
    }
}
