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
    #peraleks_error_box_wrapper_button {
        position: fixed;
        z-index: 99999999;
        padding: 15px 10px 15px 32px;
        top: 45%;
        left: -30px;
        border-radius: 0 50% 50% 0 ;
        background-color: #12b700;
        font-size: 20px;
        font-family: Consolas, monospace;
        font-weight: bold;
        cursor: hand;
        color: #ff0;
        text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.4), 0 0 1px #000;
        box-shadow: inset 0 -7px 30px rgba(0, 0, 0, 0.7), -3px 7px 15px rgba(0, 0, 0, 0.8);
    }

    #peraleks_error_box_wrapper_button.open{
        opacity: 0.1;
    }

    #peraleks_error_box_wrapper_button:hover {
        opacity: 0.6;
    }

    #peraleks_error_box_wrapper {
        all: initial;
        width: 100%;
        z-index: 9999999;
        position: fixed;
        overflow: auto;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-color: #777777;
    }

    #peraleks_error_box_wrapper.hidden {
        display: none;
    }
</style>
<div id="peraleks_error_box_wrapper_button" class="<?= $hideView ?: 'open' ?>">
    <?= $count ?>
</div>
<div id="peraleks_error_box_wrapper" class="<?= $hideView ?>">
<script>
    (function () {
        var errorButton = document.getElementById('peraleks_error_box_wrapper_button');
        var wrapper = document.getElementById('peraleks_error_box_wrapper');
        errorButton.onclick = function () {
            wrapper.classList.toggle('hidden');
            errorButton.classList.toggle('open');
        };
    })();
</script>
    <?php
    foreach ($errors as $error) :
        echo $error;
    endforeach;
    ?>
</div>
