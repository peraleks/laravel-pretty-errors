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

?>
<style type="text/css">
    * {
        margin: 0;
        padding: 0;
    }

    body, html {
        all: initial;
        height: 100%;
        overflow: hidden;
    }

    .container {
        all: initial;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
        z-index: 999999;
        position: fixed;
        overflow: hidden;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-color: #ccc
    }

    .message {
        text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.9), 0 0 1px #000;
        font-size: 30px;
        font-family: consolas, monospace;
        color: #ddd;
        background-color: #555;
        padding: 0.7em 1.2em;
        box-shadow: 4px 4px 7px rgba(0, 0, 0, 0.4);
    }
</style>
<div class="container">
    <div class="message">Page not found</div>
</div>
