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
use Peraleks\LaravelPrettyErrors\Notifiers\AbstractNotifier;

/**
 * Class Helper
 *
 * Помощник.
 * Здесь находится весь остальной функционал контроллера обработки ошибок,
 * который оказалось возможным вынести из ErrorHandler, для снижения оверхэда.<br>
 * Регистрирует функции для обработки внутренних ошибок.
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

    private static $selfError;

    /**
     * Hendler constructor.
     */
    private function __construct() {}


    /**
     * Инстанцирует ConfigObject.
     *
     * Вызов должен производится извне, а не из конструктора, так как
     * фатальная ошибка в конфигурационном файле приведёт к тому,
     * что Helper не будет инстанцирован.
     */
    public static function createConfigObject(ErrorObject $e, string $configFile)
    {
        try {
            set_error_handler([PrettyHandler::class, 'error']);

            self::$configObject = new ConfigObject($configFile);

        } catch (\Throwable $configError) {
            self::exception($e);
            self::exception(new ErrorObject($configError));
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Запускает обработку ошибки.
     *
     * Оборачивает объект ошибки в ErrorObject.
     * Если не было ошибки в конфигурационном файле
     * запускает механизм уведомления, иначе передает
     * ErrorObject во внутренний обработчик ошибок.
     *
     * @param \Throwable $e       объект ошибки
     * @param string     $logType тип ошибки
     * @return stirng
     */
    public static function format(\Throwable $e, string $configFile): string
    {
        $errorObject = new ErrorObject($e);

        self::$configObject ?: self::createConfigObject($errorObject, $configFile);

        if (self::$selfError) return self::$selfError;

        return self::getHtml($errorObject, self::$configObject);
    }

    /**
     * Реализует механизм уведомления.
     *
     * Инстанцирует классы уведомителей, которые определены в конфигурационных файлах.<br>
     * Класс уведомителя должен расширять AbstractNotifier.<br>
     * Запускает на выполнение каждого уведомителя и, в случае ошибки,
     * отправляет текущий errorObject и саму ошибку во внутренний обработчик.<br>
     *
     * @param ErrorObject  $errorObject  объект ошибки (wrapper)
     * @param ConfigObject $configObject объект конфигурации
     */
    private static function getHtml(ErrorObject $errorObject, ConfigObject $configObject): string
    {
        $html = '';
        foreach ($configObject->getNotifiers() as $notifierClass => ${0}) {
            try {
                set_error_handler([PrettyHandler::class, 'error']);

                if (!is_string($notifierClass)) {
                    throw new ErrorHandlerException(
                        'Notifiers name must be a string, '.gettype($notifierClass).' given'
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
                self::exception(new ErrorObject($e));
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
     * Конвертирует ошибку в исключение и передает в $this->exception().
     *
     * @param int    $code    код ошибки
     * @param string $message сообщение ошибки
     * @param string $file    полное имя файла ошибки
     * @param int    $line    номер строки
     * @return bool true
     */
    public static function error($code, $message, $file, $line)
    {
        self::exception(new ErrorObject(new \ErrorException($message, $code, $code, $file, $line)));
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
