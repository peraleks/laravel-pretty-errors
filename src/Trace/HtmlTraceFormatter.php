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

namespace Peraleks\LaravelPrettyErrors\Trace;

/**
 * Class HtmlTraceFormatter
 *
 * Форматирует стек вызовов в HTML.
 */
class HtmlTraceFormatter extends AbstractTraceFormatter
{
    const FILE       = '<td class="trace_file">%s</td>';
    const PATH       = '<td class="trace_path">%s</td>';
    const LINE       = '<td class="trace_line">%s</td>';
    const CLASS_NAME = '<td class="trace_class">%s</td>';
    const CALL_TYPE  = '<td class="trace_call_type">%s</td>';
    const N_SPACE    = '<td class="trace_name_space">%s</td>';
    const FUNC       = '<td class="trace_function">%s</td>';
    const PARAMS     = '<td class="trace_function_params">%s</td>';

    const ARGS     = '<td class="trace_args">%s</td>';
    const NUM      = '<td class="trace_args numeric">%s</td>';
    const BOOL     = '<td class="trace_args bool">%s</td>';
    const CALL     = '<td class="trace_args callable">%s<div class="tooltip_wrap hidden">%s</div></td>';

    const TOOLTIP  = 'tooltip_wrap';
    const STRING   = '<td class="trace_args string tooltip"><span>%s&prime;</span>%s<span>&prime;</span>'
                    .'<div class="%s hidden string"><span>&prime;</span>%s<span>&prime;</span></div></td>';

    const ARR      = '<td class="trace_args array tooltip">%s<div class="tooltip_wrap hidden">%s</div></td>';
    const RESOURCE = '<td class="trace_args resource tooltip">%s<div class="tooltip_wrap hidden">%s</div></td>';

    const S_CLASS_NAME = '<span class="trace_class">%s</span>';
    const S_N_SPACE    = '<span class="trace_name_space">%s</span>';

    const TABLE = '<table>%s</table>';
    const TR    = '<tr>%s</tr>';
    const TD    = '<td>%s</td>';

    const QUOTES = '<span class="string_quotes">&prime;</span>';
    const BREAK  = '<span class="string_quotes">%s</span>';
    const ETC    = '<span class="etc">...</span>';

    const DOC      = '<span class="doc">*</span><div class="doc_wrap hidden"><div class="doc_window">'
                    .'<div class="doc_data">%s</div><div class="doc_text">%s</div></div></div>';

    const DOC_TAG  = '<span class="doc_tag">%s</span>';
    const DOC_VAR  = '<span class="doc_var">%s</span>';
    const DOC_TYPE = '<span class="doc_type">%s</span>';
    const DOC_HREF = '<a href="%s" class="doc_href" target="_blank">%s</a>';

    const ILLUM   = '<span class="illuminate" title="%s">%s</span>';
    const ILLUM_S = '<span class="illuminate_space">%s</span>';

    /**
     * Максимальное количесво символов, отображаемое в ячейке таблицы,
     * для строковых значений аргументов функций и методов.
     *
     * @var int
     */
    protected $stringLength = 80;

    /**
     * Максимальное количество символов, отображаемое в блоке
     * расширенного просмотра для строк длинна которых больше
     * чем $this->stringLength
     *
     * @var int
     */
    protected $tooltipLength = 1000;

    /**
     * Глубина вложенности массивов при расширенном просмотре.
     *
     * @var int
     */
    protected $arrayLevel = 2;

    /**
     * Указатель текущей глубины рекурсивного обхода массива.
     *
     * @var int
     */
    protected $recursion = 0;

    /**
     * Валидирует параметры конфигурации 'arrayLevel', 'stringLength', 'tooltipLength'.
     */
    protected function before()
    {
        !is_int($level  = $this->configObject->get('arrayLevel')) ?: $this->arrayLevel = $level;
        !is_int($length = $this->configObject->get('stringLength')) ?: $this->stringLength = $length;
        !is_int($length = $this->configObject->get('tooltipLength')) ?: $this->tooltipLength = $length;
    }

    /**
     * Формирует html-таблицу.
     *
     * @param array $traceArray предварительно отформатированный стек
     * @return string
     */
    protected function completion(array $traceArray): string
    {
        $trace = '';
        foreach ($traceArray as $v) {
            $tr = $v['file'].$v['line'].$v['class'].$v['function'];

            isset($v['args']) ?: $v['args'] = [];

            for ($k = 0; $k < $this->maxNumberOfArgs; ++$k) {
                $tr .= $v['args'][$k] ?? sprintf(static::ARGS, '');
            }
            $trace .= sprintf(static::TR, $tr);
        }
        return sprintf(static::TABLE, $trace);
    }

    /**
     * Возвращает форматированное имя файла.
     *
     * Разбивает полное имя файла на два столбца таблиы (1 -путь без имени, 2 - имя)
     * Вычетает полное имя корневой директрории приложения
     * из полного имени файла для экономии места в таблице.
     *
     * @param string $file полное имя файла
     * @return string
     */
    protected function file(string $file): string
    {
        if ('' === $file) return sprintf(static::PATH, $file).sprintf(static::FILE, '');
        $parts = explode(DIRECTORY_SEPARATOR, $file);

        /* получаем имя файла без пути */
        $file = sprintf(static::FILE, '/'.array_pop($parts));

        /* получаем путь без имени файла относительно корня приложения  */
        $path = preg_replace('#^'.$this->configObject->getAppDir().'#', '', implode('/', $parts));
        if (0 !== preg_match('#^(/vendor/laravel/framework/src/)(Illuminate)(.+)$#', $path, $arr)) {
            $path = sprintf(static::ILLUM, $arr[1], $arr[2]).$arr[3];
        }
        $path = sprintf(static::PATH, $path);

        return $path.$file;
    }

    /**
     * Возвращает форматированный номер строки ошибки.
     *
     * @param int $line номер строки
     * @return string
     */
    protected function line(int $line): string
    {
        $line !== 0 ?: $line = '';
        return sprintf(static::LINE, $line);
    }

    /**
     * Возвращает форматированное имя класса и тип вызова метода.
     *
     * Разбивает имя класса на два столбца таблицы (1 - пространствоо имен, 2 - имя).
     *
     * @param string $class имя класса
     * @param string $type  тип вызова метода (:: | ->)
     *
     * @return string
     */
    protected function className(string $class, string $type): string
    {
        $parts = explode('\\', $class);

        /* получаем имя класса без пространства имён */
        $className = array_pop($parts);

        if ('' !== $class) {
            $r = new \ReflectionClass($class);
            if ($doc = $r->getDocComment()) {
                $doc = $this->formatDocToHtml($doc);
                $name = $r->getName();
                !$doc ?: $className .= sprintf(static::DOC, $name, $doc);
            }
        }
        $class = sprintf(static::CLASS_NAME, $className);

        /* получаем пространство имён без имени класса */
        $parts[] = '';
        if ($parts[0] === 'Illuminate') $parts[0] = sprintf(static::ILLUM_S, 'Illuminate');
        $nameSpace = sprintf(static::N_SPACE, implode('\\', $parts));

        /* тип вызова функции */
        $type = sprintf(static::CALL_TYPE, $type);

        return $nameSpace.$class.$type;
    }

    /**
     * Возвращает форматированнцй PHPDoc метода или класса в HTML
     *
     * @param string $doc PHPDoc
     * @return string
     */
    protected function formatDocToHtml(string $doc): string
    {
        $doc = preg_replace('/\t/', '    ', $doc);

        /* удаляем спецсимволы комментария (/** * /) и нормализуем окончание строк */
        $doc = preg_replace('/(\r\n *\*)|(\n *\*)|(\r *\*)/', "\n", $doc);
        $doc = preg_replace('/(^.*?\/\*\*(?:\n))|(\/$)/', '', $doc);

        $doc = htmlentities($doc, ENT_SUBSTITUTE | ENT_COMPAT);

        /* выделяем названия типов */
        $doc = preg_replace(
            '/^ *((?:@return|@throws|@param) +)([^ \n]+)( +)?(.*)$/m',
                '$1'.sprintf(static::DOC_TYPE, '$2').'$3$4', $doc
        );

        /* выделяем теги PHPDoc */
        $doc = preg_replace('/^ *(@[^ ]+)( +)?(.+)?$/m', sprintf(static::DOC_TAG, '$1').'$2$3', $doc);

        /* выделяем имена переменных */
        $doc = preg_replace('/(@.*?)(\$.*?)(\s)/', '$1'.sprintf(static::DOC_VAR, '$2').'$3', $doc);

        /* выделяем ссылки */
        $doc = preg_replace('#(http(?:s)?://.*?)( |&lt;|\n)#', sprintf(static::DOC_HREF, '$1', '$1').'$2', $doc);

        /* каждый второй пробел меняем на нервзрывный пробел html,
         * для сохранения всех пробелов с возможностью переноса по словам */
        $doc = preg_replace('/ {2}/', ' &nbsp;', $doc);

        return str_replace("\n", '<br>', $doc);
    }

    /**
     * Возвращает форматированное имя функции и количество аргументов.
     *
     * @param string $function имя метода или функции
     * @param string $param    пустая строка или строка вида 'a.b'
     *                         где a - количество аргументов функции,
     *                         b - количество обязателных аргументов
     * @param string $doc      PHPDoc
     * @return string
     */
    protected function functionName(string $function, string $param, string $doc): string
    {
       if ('' !== $doc) {
           $function .= sprintf(static::DOC, $function, $this->formatDocToHtml($doc));
       }
        return sprintf(static::FUNC, $function).sprintf(static::PARAMS, $param);
    }

    /**
     * Возвращает форматированное значение строкового аргумента.
     *
     * @param string $arg значение строкового аргумента
     * @return string
     */
    protected function stringArg($arg): string
    {
        $length = mb_strlen($arg);
        $string = mb_substr($arg, 0, $this->stringLength);
        $string = htmlentities($string, ENT_SUBSTITUTE | ENT_COMPAT);
        $string = preg_replace('/ /', '&nbsp;', $string);

        /* визуализируем окончания строк и удвляем во избежание переноса в ячейке таблицы */
        $string = str_replace("\r\n",  sprintf(static::BREAK, '\r\n'), $string);
        $string = str_replace("\n",  sprintf(static::BREAK, '\n'), $string);
        $string = str_replace("\r",  sprintf(static::BREAK, '\r'), $string);

        if ($length > $this->stringLength) {
            $tooltip = mb_substr($arg, 0, $this->tooltipLength);
            $tooltip = htmlentities($tooltip, ENT_SUBSTITUTE | ENT_COMPAT);

            /* каждый второй пробел меняем на нервзрывный пробел html,
             * для сохранения всех пробелов с возможностью переноса по словам */
            $tooltip = preg_replace('/ {2}/', ' &nbsp;', $tooltip);

            /* визуализируем окончания строк и делаем переносы для HTML */
            $tooltip = str_replace("\r\n",  sprintf(static::BREAK, '\r\n<br>'), $tooltip);
            $tooltip = str_replace("\n",  sprintf(static::BREAK, '\n<br>'), $tooltip);
            $tooltip = str_replace("\r",  sprintf(static::BREAK, '\r<br>'), $tooltip);

            if ($length > $this->tooltipLength) {
                $tooltip .= static::ETC;
            }
            $end = static::ETC;
            $css_class = static::TOOLTIP;
        } else {
            $tooltip = $end = '';
            $css_class = '';
        }
        return sprintf(static::STRING, $length, $string.$end, $css_class, $tooltip);
    }

    /**
     * Возвращает форматированное значение числового аргумента.
     *
     * @param int|float $arg значение числового аргумента
     * @return string
     */
    protected function numericArg($arg): string
    {
        return sprintf(static::NUM, $arg);
    }

    /**
     * Возвращает рекурсивно форматированный аргумент массив.
     *
     * @param array $arg массив
     * @return string
     */
    protected function arrayArg($arg): string
    {
        if ($this->recursion > $this->arrayLevel) {
            return sprintf(static::ARGS, static::ETC);
        }
        ++$this->recursion;
        $tooltip = $this->arrayHandler($arg);
        --$this->recursion;
        return sprintf(static::ARR, 'array['.count($arg).']', $tooltip);
    }

    /**
     * Возвращает форматированный массив для отображения ввиде HTML.
     *
     * @param array $array массив аргумента из стека вызовов
     * @return string
     */
    protected function arrayHandler(array $array): string
    {
        $tr = '';
        foreach ($array as $key => $value) {
            $key = htmlentities((string)$key, ENT_SUBSTITUTE | ENT_COMPAT);
            $key = preg_replace('/\s/', '&nbsp;', $key);
            $tr .= sprintf(static::TD, $key);

            /* останавливаем рекурсию $GLOBALS */
            if ($value === $GLOBALS) {
                $tr .= sprintf(static::ARGS, static::ETC);
                $tr = sprintf(static::TR, $tr);
                continue;
            }
            if (is_string($value))       $tr .= $this->stringArg($value);
            elseif (is_numeric($value))  $tr .= $this->numericArg($value);
            elseif (is_array($value))    $tr .= $this->arrayArg($value);
            elseif (is_bool($value))     $tr .= $this->boolArg($value);
            elseif (is_null($value))     $tr .= $this->nullArg();
            elseif ($value instanceof \Closure) $tr .= $this->callableArg($value);
            elseif (is_object($value))   $tr .= $this->objectArg($value);
            elseif (is_resource($value)) $tr .= $this->resourceArg($value);
            elseif ($cr = $this->isClosedResource($value)) $tr .= $this->closedResourceArg($cr);
            else $tr .= $this->otherArg($value);
            $tr = sprintf(static::TR, $tr);
        }
        return sprintf(static::TABLE, $tr);
    }

    /**
     * Возвращает форматированное значение аргумента null.
     *
     * @return string форматированное  'null'
     */
    protected function nullArg(): string
    {
        return sprintf(static::BOOL, 'null');
    }

    /**
     * Возвращает форматированное булево значение аргумента.
     *
     * @param bool $arg
     * @return string форматированное 'true' | 'false'
     */
    protected function boolArg($arg): string
    {
        return sprintf(static::BOOL, $arg === true ? 'true' : 'false');
    }

    /**
     * Возвращает форматированное значение аргумента callable.
     *
     * Добавляет возможность просмотра кода функции,
     * значения $this и имени файла.
     *
     * @param \Closure $arg значение аргумента callable
     * @return string
     */
    protected function callableArg($arg): string
    {
        $r = new \ReflectionFunction($arg);
        $arr = [];
        $start = $r->getStartLine();
        $end = $r->getEndLine();
        $fileName = $r->getFileName();
        $arr['code'] = array_slice(file($fileName), $start - 1, $end - $start + 1, true);
        $arr['this'] = $r->getClosureThis();
        $arr['file name'] = $fileName;

        return sprintf(static::CALL, $r->getName(), $this->arrayHandler($arr));
    }

    /**
     * Возвращает форматированное значение аргумента object.
     *
     * Добавляет PHPDoc если таковой присутсвует в коде.
     *
     * @param object $arg значение аргумента object
     * @return string
     */
    protected function objectArg($arg): string
    {
        $class = get_class($arg);
        $parts = explode('\\', $class);

        /* получаем имя класса без пространства имён */
        $className = array_pop($parts);

        $r = new \ReflectionClass($class);
        if ($doc = $r->getDocComment()) {
            $doc = $this->formatDocToHtml($doc);
            !$doc ?: $className .= sprintf(static::DOC, $class, $doc);
        }

        /* пространство имён без имени класса */
        if ($parts[0] === 'Illuminate') $parts[0] = sprintf(static::ILLUM_S, 'Illuminate');
        $space = sprintf(static::S_N_SPACE, implode('\\', $parts).'\\');

        return sprintf(static::ARGS, $space.sprintf(static::S_CLASS_NAME, $className));
    }

    /**
     * Возвращает форматированное значение аргумента resource.
     *
     * @param resource $arg значение аргумента resource
     * @return string
     */
    protected function resourceArg($arg): string
    {
        $res = 'resource';
        ob_start();
        echo $arg;
        if (preg_match('/^Resource id (\#\d+)$/', ob_get_clean(), $arr)) {
            $res .= $arr[1];
        }
        return sprintf(static::RESOURCE, $res , $this->arrayHandler(stream_get_meta_data($arg)));
    }

    /**
     * Возвращает форматированную строку типа 'closed resource #...'
     *
     * @param string $string 'closed resource #...'
     * @return string
     */
    protected function closedResourceArg(string $string): string
    {
        return sprintf(static::RESOURCE, $string, '');
    }

    /**
     * Возвращает форматированное значение аргумента неизвестного типа.
     *
     * @param mixed $arg значение аргумента неизвестного типа
     * @return string
     */
    protected function otherArg($arg): string
    {
        return sprintf(static::ARGS, gettype($arg));
    }
}
