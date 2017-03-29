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

$development = [

    \Peraleks\LaravelPrettyErrors\Notifiers\HtmlNotifier::class => [
        'enabled'       => true,
        'handleTrace'   => E_ALL,
//        'simpleTrace'   => true,
        'hideTrace'     => true,
        'fontSize'      => 15,
        'stringLength'  => 80,
        'tooltipLength' => 1000,
        'arrayLevel'    => 2,
    ],

    \Peraleks\LaravelPrettyErrors\Notifiers\BrowserConsoleNotifier::class => [
        'enabled'        => true,
//        'handleTrace'    => E_ALL,
//        'phpNativeTrace' => true,
        'console'        => 'log',
    ],
];

$production = [

    \Peraleks\LaravelPrettyErrors\Notifiers\ProductionNotifier::class => [
        'enabled'     => true,
        'includeFile' => '',
        'timeFormat'  => 'o-m-d H:i:s',
    ],
];



if (config('app.debug')) {
    $arr = $development;
} else {
    $arr = $production;
}


return [
    'SELF_LOG_FILE'   => storage_path().'/logs/laravel.log',
    'NOTIFIERS'       => $arr,
];

