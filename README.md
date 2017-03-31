<a href="https://packagist.org/packages/peraleks/laravel-pretty-errors"><img src="https://poser.pugx.org/peraleks/laravel-pretty-errors/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/peraleks/laravel-pretty-errors"><img src="https://poser.pugx.org/peraleks/laravel-pretty-errors/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/peraleks/laravel-pretty-errors"><img src="https://poser.pugx.org/peraleks/laravel-pretty-errors/license.svg" alt="License"></a>
# LaravelPrettyErrors
Error formatter for Laravel 5.1 or later. Provides a convenient display as HTML-page and in the browser console.
Provides an enhanced view of the stack trace (viewing the contents of the arguments: **array**, **closure**, **resource**, **string**;
and view **PHPDoc** classes and methods). Provides the opportunity to configure custom pages **404** and **500**.

![](https://raw.githubusercontent.com/peraleks/laravel-pretty-errors/master/images/1.png)
![](https://raw.githubusercontent.com/peraleks/laravel-pretty-errors/master/images/2.png)
![](https://raw.githubusercontent.com/peraleks/laravel-pretty-errors/master/images/3.png)

## Install
```bash
$ composer require peraleks/laravel-pretty-errors
```

## Usage
Copy file **_vendor/peraleks/laravel-pretty-errors/src/Config/pretty-errors.php_** to Laravel config_path
(**_app/config_**).

Add code to **_App\Exceptions\Handler_** :
```php
use Peraleks\LaravelPrettyErrors\Core\PrettyHandler;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;


    protected function convertExceptionToResponse(Exception $e)
    {
        $html = PrettyHandler::format($e, config_path('pretty-errors.php'));

        $e = FlattenException::create($e);

        return SymfonyResponse::create($html, $e->getStatusCode(), $e->getHeaders());
    }
```

## Configuration
File **_pretty-errors.php_**

```php
$development = [

    \Peraleks\LaravelPrettyErrors\Notifiers\HtmlNotifier::class => [
        'enabled'       => true,   // [bool] switch error display as html
        'handleTrace'   => E_ALL,  // [int]  switch trace processing (bitwise mask)
        'simpleTrace'   => true,   // [bool] switch display arguments in trace
        'hideTrace'     => true,   // [bool] switch trace display on start
        'fontSize'      => 15,     // [int]  main font size in pixels (works as a scale)
        'stringLength'  => 80,     // [int]  line length in cells
        'tooltipLength' => 1000,   // [int]  line length in extended view
        'arrayLevel'    => 2,      // [int]  nesting level of arrays
    ],

    \Peraleks\LaravelPrettyErrors\Notifiers\BrowserConsoleNotifier::class => [
        'enabled'        => true,  // [bool]   switch error display in browser console
        'handleTrace'    => E_ALL, // [int]    switch trace processing (bitwise mask)
        'phpNativeTrace' => true,  // [bool]   switch PHP native trace display 
        'console'        => 'log', // [string] browser console section (error|warn|info|log|debug)
    ],
];



$production = [

    \Peraleks\LaravelPrettyErrors\Notifiers\ProductionNotifier::class => [
        'enabled' => true,         // [bool]   switch error page display in production
        'file404' => '',           // [string] fully qualified file name or blade-template name
        'file500' => '',           // [string] fully qualified file name or blade-template name
        
        /* For blade-template, write 'view.404' where '404' is the name for 404.blade.php .
         You can use native PHP template. To do this, enter the fully qualified file name.
          
         The file may not be a template, but must return or print a string.
         For example, a file can contain such a code:
         
         return "<h2>Page not found</h2>";
         
         or
         
         echo view('404')->render();
         */
    ],
];
```

## License

The MIT License ([MIT](LICENSE.md)).

[link-zip]: https://github.com/peraleks/laravel-pretty-errors/archive/master.zip
[link-author]: https://github.com/peraleks

