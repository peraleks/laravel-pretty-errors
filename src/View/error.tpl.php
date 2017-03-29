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

$id = 'peraleks_wrap'.rand(); ?>
<br>
<style>
    <?= $style ?>
</style>
<div class="<?= $cssType ?> peraleks_error_box" style="font-size: <?= $fontSize ?>px">
    <div class="header">
        <?= $type.' ['.$code.'] ' ?>
    </div>
    <div class="text">
        <?= $message ?>
    </div>
    <div class="file">
        <span title="<?= $path ?>"><?= $file ?></span><span class="bracket">(</span><span
                class="line"><?= $line ?></span><span class="bracket">)</span>
        <?php if ($trace != '') : ?>
            <div class="but_trace" onclick="parentNode.nextElementSibling.classList.toggle('hidden')">
                trace <?= $traceCount ?>
            </div>
        <?php endif; ?>
    </div>
    <div id="<?= $id ?>" class="peraleks_tw <?= $hidden ?>">
        <?= $trace ?>
    </div>
</div>
<?php if ($trace != '') : ?>
    <script>
    var resDocText_peraleks_var;
        (function () {
            var wrap = document.getElementById('<?= $id ?>');

            wrap.addEventListener('click', function (e) {
                var target = e.target;

                if (target.classList.contains('doc_wrap')) {
                    target.classList.toggle('hidden');
                    resDocText_peraleks_var.querySelector('.doc_text').style.height = '';
                } else {
                    var children = target.children;

                    for (var i = 0; i < children.length; i++) {

                        if (children[i].classList.contains('doc_wrap')) {
                            var doc = children[i];
                            doc.classList.toggle('hidden');
                            resDocText_peraleks_var = doc;
                            resize(doc);
                            break;
                        }
                    }
                }

                var tooltip = target.querySelector('.tooltip_wrap');
                if (null != tooltip) {
                    tooltip.classList.toggle('hidden');
                }
            });

        function resize(doc) {
            var docWindow = doc.querySelector('.doc_window');
            var docData = doc.querySelector('.doc_data');
            var docText = doc.querySelector('.doc_text');
            if (docText.clientHeight > ((doc.clientHeight - docData.clientHeight) * 0.93)) {
                docText.style.height = ((doc.clientHeight - docData.clientHeight) * 0.90) +'px';
            }
        }

        window.onresize = function () {
            if (null != resDocText_peraleks_var) {
                resDocText_peraleks_var.querySelector('.doc_text').style.height = '';
                resize(resDocText_peraleks_var);
            }
        }

        })();
    </script>
<?php endif; ?>
<br>