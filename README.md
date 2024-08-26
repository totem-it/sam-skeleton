<p align="center"><img src="logo.svg" width="400" alt=""></p>

SAM-skeleton
================

Basic elements for sam application.

## General System Requirements

- [PHP ^8.2](http://php.net/)
- [Laravel ^11.0](https://github.com/laravel/framework)

## Installation

If necessary, use the composer to download the library

```bash
$ composer require totem-it/sam-skeleton
```

Remember to put repository in a composer.json

```
"repositories": [
    {
        "type": "vcs",
        "url":  "https://github.com/totem-it/sam-skeleton.git"
    }
],
```

---

## Usage

Functionalities are organized into packages within the src/ folder.

- [Translation](#translation)

- [Themes](#themes)

- Features
    - todo add all major functionalities like /DataTable or /QueryFilter

- Casts
    - [SanitizeField](#SanitizeField)
    - [AsArray](#AsArray)

- Services
    - [CSVService](#CSVService)
    - [QueryFilter](#QueryFilter)
    - [Barcode](#Barcode)
    - [SimpleXML](#SimpleXml)
    - [Pdf](#Pdf)
    - [TaskCommand](#TaskCommand)

- Concerns
    - [TraitServiceProvider](#TraitServiceProvider )
    - [EloquentDecorator](#EloquentDecorator)
    - [SqlMigrator](#SqlMigrator)
    - [ParseValueObject](#ParseValueObject)

- Middleware
    - [LocalizationMiddleware](#LocalizationMiddleware)
    - [ForceJsonMiddleware](#ForceJsonMiddleware)
    - [TrustOnlyAuthenticated](#TrustOnlyAuthenticated)

- Others
    - [Webhook](#Webhook)
    - [Exceptions](#Exceptions)
    - [RepositoryException](#RepositoryException)

---

## Translation

Provides a streamlined way to handle basic translations in application. This allows to effortlessly manage and retrieve
translation strings for different languages, ensuring your application is accessible to a global audience.

---

## Themes

Provides a structured approach to managing the visual aspects of application based on CSS/SCSS.

---

## Features

---

## Casts

### SanitizeField

It ensures the security and cleanliness of data that is saved in the database by automatically cleansing it using the
Purify tool. The getPurifier method retrieves purify instances from the Laravel container and configures them using the
/config/config.php file

example:

```php
use Illuminate\Database\Eloquent\Model;
use Totem\SameSkeleton\App\Casts\SanitizeField;

class Post extends Model
{
    protected $casts = [
        'content' => SanitizeField::class,
    ];
}
```

### AsArray

It is used to store model attributes as arrays in the database as JSON and to convert this data back to arrays on
read.
This makes it easy to work with arrays in Eloquent models, and the data is automatically saved as JSON in the
database.

### ParameterCast

It allows manipulation of Eloquent model attributes by transforming them into a `ParametersService` object. Thanks to
this, you can perform operations on data attributes while maintaining their appropriate form in the database (as JSON).

---

## Services

### CSVService

You can download data as CSV (response object return)

```php
return (new \Totem\SamSkeleton\App\Services\CSVService())
    ->streamDownload($collection, $filename, $http_header);
```

or

```php
return \Totem\SamSkeleton\App\Services\CSVService::download($collection, $filename, $http_header);
```

Ad default, the collection keys are used for header. It is possible to create custom headers

```php
$csv = new \Totem\SamSkeleton\App\Services\CSVService();
$csv->setRowHeaders(['id', 'email']);
```

You can change basic CSV parameters like DELIMITER, ENCLOSURE, ESCAPE

```php
new \Totem\SamSkeleton\App\Services\CSVService($separator, $enclosure, $escape);
```

or

```php
(new \Totem\SamSkeleton\App\Services\CSVService())
    ->setDelimiter($separator)
    ->setEnclosure($enclosure)
    ->setEscape($escape);
```

### QueryFilter

!!! todo

### Barcode

Simplifies the process of generating various types of barcodes and allows for flexible customization of their appearance
and format.

example:

```php
use Totem\SamSkeleton\App\Services\Barcode;

// Generate a Code 39 barcode as HTML
$htmlBarcode = Barcode::c39('123456789')->asHTML(2, 30);

// Generate an EAN-13 barcode as PNG with a label
$pngBarcode = Barcode::ean13('0123456789012')->hasLabel()->asPNG(3, 50);

// Generate a QR code as SVG
$svgBarcode = Barcode::qr('https://example.com')->asSVG(4, 4);
```

### SimpleXML

Package extends PHP's native SimpleXMLElement class, providing additional functionality for creating and working with
XML data.

To create a SimpleXML object from an XML string, use the `from` method. This method can handle additional options,
whether the string is a URL, and namespace or prefix settings

```php
$xmlString = '<root><item key="value">Content</item></root>';
$simpleXML = SimpleXML::from($xmlString);
```

Use the `get` method to safely retrieve values from the XML structure. This method ensures that the value is returned as
a string

```php
$value = $simpleXML->get('item');
```

### Pdf

Package extends the functionality of SnappyPdf provided by the barryvdh/laravel-snappy package. This service is a
simplified and enhanced interface for generating and testing PDF.

### TaskCommand

This service provides methods for setting and retrieving output messages, as well as formatting and printing
information.

---

## Concerns

### TraitServiceProvider

Add trait `\Totem\SamSkeleton\App\Traits\TraitServiceProvider` to your package service provider file.

Use abstract method for namespace your package used to load and publishing

```php
public static function getNamespace(): string
```

Helper for router pattern register

```php
$this->registerRoutePattern($key, $pattern);
```

Helper for mass class binding

```php
$this->configureBinding([$interface => $class]);
```

[DEPRECATED] ~~Helper for load and publish base components of package~~ instead use
built-in `Illuminate\Support\ServiceProvider` ( `loadJsonTranslationsFrom()`, `loadMigrationsFrom()`, `loadViewsFrom()` )

```php
$this->loadAndPublish([
    string $language = null, 
    string $migration = null, 
    string $view = null,
    string $route = null
]);
```

### EloquentDecorator

If you need to morph your class when get from other model use `\Totem\SamSkeleton\App\Traits\EloquentDecoratorTrait`

example:

```php
class User extends Model 
{
    use EloquentDecoratorTrait;
    
    protected function getDecoratorClassMap(): array
    {
        return [
            Employee::class
            Worker::class
        ];
    }
}
```

### SqlMigrator

This trait simplifies migration logic by providing reusable methods that handle common database operations,
especially when dealing with MySQL-specific features or checking for foreign key constraints.

example:

```php
class ExampleMigration
{
    use SqlMigrator;

    public function up()
    {
        Schema::create('example_table', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(self::uuid());
            $table->unsignedBigInteger('related_id');

            if (!self::hasForeignKey('example_table', 'related_id')) {
                $table->foreign('related_id')->references('id')->on('related_table');
            }
        });

        self::isMysql(function () {
            // Execute MySQL-specific migration logic
        }, function () {
            // Execute non-MySQL migration logic
        });
    }
}
```

### ParseValueObject

Useful in value objects or data transfer objects (DTOs) where you often need to validate and parse input data before
using it. It provides a simple and reusable way to handle common parsing scenarios.

example:

```php
$intValue = ParseValueObject::intOrNull('123'); // Returns 123
$nullValue = ParseValueObject::intOrNull(null); // Returns null
```

---

## Middleware

### LocalizationMiddleware

By Default SAM-skeleton uses localization from `.env APP_LOCALE` key. To change response of API, add
header `Accept-Language`

example:

```php
Route::middleware(\Totem\SamSkeleton\App\Middleware\LocalizationMiddleware::class)->get('/', [MyController::class, 'index']);
```

### ForceJsonMiddleware

Middleware that ensures all incoming HTTP requests to your Laravel application expect a JSON response.

example of use in provider:

```php
  $this->getRouter()->prependMiddlewareToGroup('api', ForceJsonMiddleware::class);
```

### TrustOnlyAuthenticated

Designed to ensure that only authenticated users who match a specific condition can proceed with the request.

---

## Others

### Webhook

Utility designed to handle and log webhook events in a Laravel application, particularly focusing on job events managed
by Laravel Horizon.

### Exceptions

Enhances Laravel's default exception handling by providing custom responses and detailed context information.
This ensures that API errors are managed effectively and that developers have the tools needed to diagnose and address
issues within their applications.

example of implementation:

```php
$this->app->bind(ExceptionHandler::class => Handler::class);
```

### RepositoryException

Extends exception throwing for repositories. The notFound and missing methods are used to signal problems with access to
data.

examples:

```php
throw new RepositoryException(__('No role id have been given.'));
```

```php
throw RepositoryException::notFound(__('Given id :code is invalid or role not exist.', ['code' => $uuid]));
```


