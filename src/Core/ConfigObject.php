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

use Peraleks\LaravelPrettyErrors\Exception\ErrorHandlerException;

/**
 * Class ConfigObject
 *
 * Валидирует конфигурационный файл.
 * Предоставляет остальным классам доступ к параметрам конфигурации.
 */
class ConfigObject
{
    /**
     * Начальная конфигураця для слияния
     * с пользовательской конфигурацией.
     *
     * @var array
     */
    private $config = [
        'SELF_LOG_FILE'   => '',
        'NOTIFIERS'       => [],
        'APP_DIR'         => '',
    ];

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
     * @throws ErrorHandlerException
     */
    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new ErrorHandlerException(
                'ErrorHandler::instance($file): $file must be a string, '.gettype($file).' given'
            );
        } elseif (!file_exists($file)) {
            throw new ErrorHandlerException(
                'Configuration file not exist: ErrorHandler::instance('.$file.')'
            );
        } elseif (!is_array($arr = include $file)) {
            throw new ErrorHandlerException(
                'The configuration file '.$file.' should return an array, '.gettype($arr).' given'
            );
        }
        $this->config = array_merge($this->config, $arr);
        $this->validateSelfLogFile($this->config['SELF_LOG_FILE']);
        $this->notifiersValidate($this->config['NOTIFIERS']);
        $this->config['APP_DIR'] = str_replace('\\', '/', base_path());
    }

    /**
     * Валидация параметра конфигурации 'SELF_LOG_FILE'.
     *
     * Значение по умолчанию: пустая строка
     *
     * @param $selfLogFile
     */
    private function validateSelfLogFile(&$selfLogFile)
    {
        is_string($selfLogFile) ?: $selfLogFile = storage_path().'/logs/laravel.log';
    }

    /**
     * Валидация параметра конфигурации 'NOTIFIERS'.
     *
     * Значение по умолчанию: array
     *
     * @param $notifiers
     */
    private function notifiersValidate(&$notifiers)
    {
        if (!is_array($notifiers)) {
            $type = gettype($notifiers);
            $notifiers = [];
            trigger_error(
                'Configuration file: value of key \'NOTIFIERS\' must be an array, '.$type.' given',
                E_USER_ERROR
            );
        }
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
        return $this->config['NOTIFIERS'];
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
        return $this->config['NOTIFIERS'][$this->currentNotifier][$param] ?? null;
    }

    /**
     * Возвращает значение конфигурации 'APP_DIR'.
     *
     * @return string
     */
    public function getAppDir(): string
    {
        return $this->config['APP_DIR'];
    }

    /**
     * Вщзвращает значение конфигурации 'SELF_LOG_FILE'.
     *
     * @return string если не задано, то пустая строка
     */
    public function getSelfLogFile(): string
    {
        return $this->config['SELF_LOG_FILE'];
    }
}
