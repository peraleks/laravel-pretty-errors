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

use Peraleks\LaravelPrettyErrors\Notifiers\AbstractNotifier;

/**
 * Class PrettyHandler
 *
 * Основной контроллер. Инстанцирует объект конфигурации.
 * Заускает процесс форматирования ошибки.
 */
class PrettyHandler
{
    /**
     * Объект конфигурации.
     *
     * @var ConfigObject
     */
    private static $configObject;

    /**
     * Объект внутреннего обработчика ошибок.
     *
     * @var SelfErrorHandler
     */
    private static $selfErrorHandler;

    /**
     * Html-страница сообщением о внутренней ошибке.
     *
     * @var string|null
     */
    private static $selfError;

    /**
     * PrettyHandler constructor.
     */
    private function __construct() {}


    /**
     * Инстанцирует ConfigObject.
     *
     * @param ErrorObject $e          объект ошибки
     * @param null|mixed  $configFile полное имя файла конфигурации
     */
    public static function createConfigObject(ErrorObject $e, $configFile = null)
    {
        try {
            set_error_handler([PrettyHandler::class, 'error']);

            self::$configObject = new ConfigObject($configFile);

        } catch (\Throwable $configError) {
            self::exception($e);
            self::exception(new InnerErrorObject($configError));
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Возвращает html-страницу c информацией об ошибке.
     *
     * Оборачивает объект ошибки в ErrorObject.
     *
     * @param \Throwable  $e          объект ошибки
     * @param null|mixed  $configFile полное имя файла конфигурации
     * @return string
     */
    public static function format(\Throwable $e, $configFile = null): string
    {
        $errorObject = new ErrorObject($e);

        self::$configObject ?: self::createConfigObject($errorObject, $configFile);

        if (self::$selfError) return self::$selfError;

        return self::getHtml($errorObject, self::$configObject);
    }

    /**
     *  Возвращает html-страницу c информацией об ошибке.
     *
     * Инстанцирует классы уведомителей, которые определены в конфигурационных файлах.<br>
     * Класс уведомителя должен расширять AbstractNotifier.<br>
     * Запускает на выполнение каждого уведомителя и, в случае ошибки,
     * отправляет текущий errorObject и саму ошибку во внутренний обработчик.<br>
     *
     * @param ErrorObject  $errorObject  объект ошибки (wrapper)
     * @param ConfigObject $configObject объект конфигурации
     * @return string
     */
    private static function getHtml(ErrorObject $errorObject, ConfigObject $configObject): string
    {
        $html = '';
        foreach ($configObject->getNotifiers() as $notifierClass => ${0}) {
            try {
                set_error_handler([PrettyHandler::class, 'error']);

                if (!is_string($notifierClass)) {
                    throw new \Exception(
                        'PrettyHandler: notifiers name must be a string, '.gettype($notifierClass).' given'
                    );
                }
                $configObject->setNotifierClass($notifierClass);

                /* проверяем включен ли Notifier */
                if (true !== $configObject->get('enabled')) continue;

                $notifier = new $notifierClass($errorObject, $configObject);

                if (!$notifier instanceof AbstractNotifier) {
                    trigger_error(
                        $notifierClass.' must extend '.AbstractNotifier::class,
                        E_USER_ERROR
                    );
                    continue;
                }

                $html .= $notifier->run()."\n";

            } catch (\Throwable $e) {
                self::exception($errorObject);
                self::exception(new InnerErrorObject($e));
            } finally {
                restore_error_handler();
            }

        }
        if (self::$selfError) return self::$selfError;

        return $html;
    }

    /**
     * Обрабатывает внутренние ошибки.
     *
     * Конвертирует ошибку в исключение и передает в self::exception().
     *
     * @param int    $code    код ошибки
     * @param string $message сообщение ошибки
     * @param string $file    полное имя файла ошибки
     * @param int    $line    номер строки
     * @return bool true
     */
    public static function error($code, $message, $file, $line)
    {
        self::exception(new InnerErrorObject(new \ErrorException($message, $code, $code, $file, $line)));
        return true;
    }

    /**
     * Обрабатывает внутренние исключения.
     *
     * Инстанцирует внутренний обработчик ошибок и передаёт ему ошибку.
     *
     * @param \Throwable|ErrorObject $e объект ошибки
     */
    public static function exception(ErrorObject $e)
    {
        self::$selfErrorHandler
            ?: self::$selfErrorHandler = new SelfErrorHandler(self::$configObject);
        self::$selfError .= self::$selfErrorHandler->format($e);
    }
}
