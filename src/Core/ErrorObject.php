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

use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class ErrorObject
 *
 * Объект ошибки. Является обёрткой над объектом \Throwable
 * и полностью повторяет его интерфейс. По средствам дополнителных методов
 * предоставляет название ошибки из её кода, а так же название функции-обработчика
 * через которую ошибка пришла. Производит сопоставление всех исключений с кодом E_ERROR,
 * а ParseError c E_PARSE для более удобного управления при помощи битовой маски.
 */
class ErrorObject
{
    /**
     * Объект ошибки клиентской части скрипта.
     *
     * @var \Throwable
     */
    protected $e;

    /**
     * Код ошибки (severity)
     *
     * @var int
     */
    protected $code;

    /**
     * Тип ошибки полученный из $this->codeName для
     * стандартных ошибок и при помощи get_type() для исключений.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Кеш массива стека вызовов с удалённым первым элементом.
     *
     * @var null | array
     */
    protected $trace;

    /**
     * Соответствие кодов ошибок их названиям.
     *
     * @var array
     */
    protected  $codeName = [
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
     * ErrorObject constructor.
     *
     * Устанавливает код ошибки (из соображения универсальности
     * управления ошибками для исключения \ParseError - E_PARSE, для остальных
     * исключений - E_ERROR).<br>
     * Также определяет тип/название ошибки.
     *
     * @param \Throwable $e объект ошибки клиентской части скрипта
     */
    public function __construct(\Throwable $e)
    {
        $this->e = $e;

        if (0 === ($this->code = $this->e->getCode())) {
            $ref = new \ReflectionClass($e);
            /* обработчик Laravel прячет код ошибки в свойство 'severity' */
            if ($ref->hasProperty('severity')) {
                $prop = $ref->getProperty('severity');
                $prop->setAccessible(true);
                $this->code = $prop->getValue($e);
            }
        }

        if ($this->e instanceof \ErrorException) {
            $this->type = $this->codeName[$this->code] ?? 'unknown';
        } else {
            $this->code = $this->e instanceof \ParseError ? E_PARSE : E_ERROR;
            $this->type = get_class($this->e);
        }
    }

    /**
     * Возвращает тип (название) ошибки.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Возвращает код ошибки (severity).
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Возвращает текст ошибки.
     *
     * @return string
     */
    public function getMessage(): string
    {
        if (method_exists($this->e, 'getStatusCode')) {
            return $this->e->getStatusCode().' '.$this->e->getMessage();
        }
        return $this->e->getMessage();
    }

    /**
     * Возвращает полное имя файла, где произошла ошибка
     * с нормализованными слешами.
     *
     * @return string
     */
    public function getFile(): string
    {
        return str_replace('\\', '/', $this->e->getFile());
    }

    /**
     * Возврашает номер строки, где произошла ошибка.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->e->getLine();
    }

    /**
     * Возвращает массив со стеком вызовов.
     *
     * @return array
     */
    public function getTrace(): array
    {
        if ($this->trace) {
            return $this->trace;
        } elseif (! $this->e instanceof FatalThrowableError) {
        /* для не FatalThrowableError удаляем первый лишний элемент
         * и кешируем, чтобы не повторять операцию сдвига массива */
            $this->trace = $this->e->getTrace();
            array_shift($this->trace);
            return $this->trace;
        } else {
            return $this->e->getTrace();
        }
    }

    /**
     * Возвращает стек вызовов ввиде строки.
     *
     * @return string
     */
    public function getTraceAsString(): string
    {
        return $this->e->getTraceAsString();
    }

    /**
     * Возвращает предыдущую ошибку.
     *
     * @return \Throwable
     */
    public function getPrevious(): \Throwable
    {
        return $this->e->getPrevious();
    }

    /**
     * Возвращает полную информацию об ошибке
     * со стеком вызовов ввиде строки.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->e;
    }
}
