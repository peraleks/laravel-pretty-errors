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
<style>
    .peralex_self_error_box {
        font-family: Consolas, monospace;
        font-size: 14px;
        text-align: left;
        padding: 0.2em;
        display: inline-block;
        position: relative;
        z-index: 10000;
        background-color: #3c8c3c;
    }

    .peralex_self_error_box .error_header {
        font-size: 110%;
        font-weight: 500;
        padding: 5px;
        color: #fff;
    }

    .peralex_self_error_box .error_header > span {
        color: yellow;
    }

    .peralex_self_error_box .error_text {
        padding: 5px 15px;
        font-family: Consolas, monospace;
        background-color: rgba(0, 0, 0, 0.5);
        color: rgba(255, 255, 255, 0.6);
        text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.4), 0 0 1px #555;
    }

    .peralex_self_error_box .error_message {
        font-size: 115%;
        color: rgba(255, 255, 255, 0.9);
    }
</style>
<br>
<div class="peralex_self_error_box">
    <div class="error_header"><span><?= $type ?></span> <?= $file.' ('.$line ?>)</div>
    <div></div>
    <div class="error_text">
        <div class="error_message"><?= $message ?></div>
        <?= $trace ?>
    </div>
</div>
<br>