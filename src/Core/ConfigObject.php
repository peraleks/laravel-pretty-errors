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
 * Class ConfigObject
 *
 * Валидирует конфигурационный файл.
 * Предоставляет остальным классам доступ к параметрам конфигурации.
 */
class ConfigObject
{
    /**
     * Массив с настройками уведомителей.
     *
     * @var array
     */
    private $notifiers;

    /**
     * Корневая директория Laravel.
     *
     * @var string
     */
    private $base_path;

    /**
     * Имя класса-уведомителя.
     *
     * $this->get() будет искать значения в масиве конфигурации
     * по этому имени и переданному ключу
     *
     * @var string
     */
    private $currentNotifier;

    /**
     * ConfigObject constructor.
     *
     * Выполняет валидацию файла конфигурации.
     *
     * @param string $file полное имя файла конфигурации
     * @throws \Exception
     */
    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new \Exception(
                'PrettyHandler::format($e, $file): $file must be a string, '.gettype($file).' given'
            );
        } elseif (!file_exists($file)) {
            throw new \Exception(
                'Configuration file not exist: PrettyHandler::format($e, '.$file.')'
            );
        } elseif (!is_array($arr = include $file)) {
            throw new \Exception(
                'PrettyHandler configuration file '.$file.' should return an array, '.gettype($arr).' given'
            );
        }
        $this->notifiers = $arr;
        $this->base_path = str_replace('\\', '/', base_path());
    }

    /**
     * Устанавливает имя текущего уведомителя
     *
     * get() будет искать значения в масиве конфигурации
     * по этому имени и переданному ей ключу
     *
     * @param string $notifierClass имя класса уведомителя
     */
    public function setNotifierClass(string $notifierClass)
    {
        $this->currentNotifier = $notifierClass;
    }

    /**
     * Возвращает массив уведомителей.
     *
     * @return array массив уведомителей
     */
    public function getNotifiers(): array
    {
        return $this->notifiers;
    }

    /**
     * Возвращает значение из конфигурационного массива по переданному ключу.
     *
     * Ищет значенияе в масиве конфигурации по двум ключам: по переданному,
     * и по имени умедомителя из $this->currentNotifier,
     * предварительно установленного setNotifierClass().
     * В случае неудачи возвращает null.
     *
     * @param string $param ключ массива конфигурации
     * @return null | string
     */
    public function get(string $param)
    {
        return $this->notifiers[$this->currentNotifier][$param] ?? null;
    }

    /**
     * Возвращает корневую директорию Laravel.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->base_path;
    }
}
