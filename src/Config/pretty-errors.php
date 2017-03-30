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
        'enabled' => true,
        'file404' => '',
        'file500' => '',
    ],
];




if (config('app.debug')) {
    return $development;
} else {
    return $production;
}

