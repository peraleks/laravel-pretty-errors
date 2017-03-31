<a href="https://packagist.org/packages/peraleks/laravel-pretty-errors"><img src="https://poser.pugx.org/peraleks/laravel-pretty-errors/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/peraleks/laravel-pretty-errors"><img src="https://poser.pugx.org/peraleks/laravel-pretty-errors/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/peraleks/laravel-pretty-errors"><img src="https://poser.pugx.org/peraleks/laravel-pretty-errors/license.svg" alt="License"></a>
# LaravelPrettyErrors
Error formatter for Laravel 5. Provides a convenient display as HTML-page and in the browser console.
Provides an enhanced view of the stack trace (viewing the contents of the arguments: **array**, **closure**, **resource**, **string**;
and view **PHPDoc** classes and methods). Provides the opportunity to configure custom pages **404** and **500**.

![](https://raw.githubusercontent.com/peraleks/laravel-pretty-errors/images/master/1.png)
![](https://raw.githubusercontent.com/peraleks/laravel-pretty-errors/images/master/2.png)
![](https://raw.githubusercontent.com/peraleks/laravel-pretty-errors/images/master/3.png)

## Install
```bash
$ composer require peraleks/laravel-pretty-errors
```

## Usage
Copy file **_vendor/peraleks/laravel-pretty-errors/src/Config/pretty-errors.php_** to Laravel config_path
(**_app/config_**).

Add code to **_App\Exceptions\Handler_**:
```php
    protected function convertExceptionToResponse(Exception $e)
    {
        $html = PrettyHandler::format($e, config_path('pretty-errors.php'));

        $e = FlattenException::create($e);

        return SymfonyResponse::create($html, $e->getStatusCode(), $e->getHeaders());
    }
```

## License

The MIT License ([MIT](LICENSE.md)).

[link-zip]: https://github.com/peraleks/laravel-pretty-errors/archive/master.zip
[link-author]: https://github.com/peraleks

