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

- [Themes](#themes)

- [Translation](#translation)

Functionalities are organized into packages within the src/Bundles folder:

- Auth
    - [AuthRequest](#AuthRequest)
    - [TrustOnlyAuthenticated](#TrustOnlyAuthenticated)
- [DataTable](#DataTable)
- Enum
    - [CollectableEnum](#CollectableEnum)
    - [ComparableEnum](#ComparableEnum)
- EloquentModel
    - [EloquentDecorator](#EloquentDecorator)
    - [SqlMigrator](#SqlMigrator)
    - [AsArrayCast](#AsArrayCast)
    - [SanitizeFieldCast](#SanitizeFieldCast)
    - [EloquentRelation](#EloquentRelation)
- [ExceptionsHandler](#ExceptionsHandler)
- FileHandler
    - [Pdf](#Pdf)
    - [SimpleXML](#SimpleXml)
    - [CSVService](#CSVService)
    -

[//]: # ( - TODO complete )

- Meta

[//]: # (- TODO meta)

- Middleware
    - [LocalizationMiddleware](#LocalizationMiddleware)
    - [ForceJsonMiddleware](#ForceJsonMiddleware)
- [Repository](#Repository)
    - [RepositoryException](#RepositoryException)
    - [Activable](#Activable)
- Resource
    - [ApiCollection](#ApiCollection)
    - [ApiResource](#ApiResource)
- Support
    - [Webhook](#Webhook)
    - [Barcode](#Barcode)
    - [TraitServiceProvider](#TraitServiceProvider )
    - [TaskCommand](#TaskCommand)
    -
- [QueryFilter](#QueryFilter)
- [ValueObject](#ValueObject)

---

## Themes

Provides a structured approach to managing the visual aspects of application based on CSS/SCSS.

---

## Translation

Provides a streamlined way to handle basic translations in application. This allows to effortlessly manage and retrieve
translation strings for different languages, ensuring your application is accessible to a global audience.

---

Bundles
---

## Auth

### AuthRequest

Extend your form request class with `\Totem\SamSkeleton\App\Bundles\AuthRequest`. Checks whether the user is authorized
to execute the request.

### TrustOnlyAuthenticated

Middleware designed to ensure that only authenticated users who match a specific condition can proceed with the request.

---

## DataTable

Sometimes it is necessary to create predefined DataTable headers or filters.

```php
$table = new \Totem\SamSkeleton\App\Bundles\DataTable\DataTableService();
```

#### Views

To create views (optional headers mutations) use:

```php
$table->addView('view key name', 'Translatable label of view');
```

or alternatively create multiple views at once

```php
$table->setViews([
    'one' => 'First view',
    'second' => 'Second view',
]);
```

This method will save the first view as **default**. To set a **default view** manually you can use
method `setDefaultView`.
The view must already be registered.

```php
$table->setDefaultView('one');
```

#### Filters

Every filter is instance of `\Totem\SamSkeleton\App\Bundles\DataTable\FilterOptions` class.

You can add filters based on select html element, using methods `addFilter()` or `setFilters()`.

```php
$table->setFilters('filter key name', new FilterOptions(
    label: 'Translatable label of filter',
    icon: 'Optional google Material Icon'
    items: [
        'value' => 'translatable label'
    ]
));

// or

$table->setFilters(array $filters);
```

`FilterOptions` accepts for items simple arrays or native php ENUMs with `CollectableEnum` trait.

```php
new FilterOptions(
    label: 'Label',
    items: Enum::class
);
// items [['text' =>'Heart, 'value' => 'H'] ...]

// or

new FilterOptions(
    label: 'Label',
    items: [1, 2, 3]
);
// items [['text' => 1, 'value' => 1] ...]
```

#### Headers

Every header is instance of `\Totem\SamSkeleton\App\Bundles\DataTable\DataTableCollection` collection
with `\Totem\SamSkeleton\App\Bundles\DataTable\HeaderOptions` as a header column.

You can add header, using methods `addHeader()`, `addHeaderColumn()` or `setHeaders()`.

```php
$table->addHeader('view key name', new HeaderOptions());
$table->addHeaderColumn('header key name', new HeaderOptions());

// or

$table->setHeaders(array $headers);
```

`HeaderOptions` accepts multiple optional options which can be used by frontend framework (like vuetify).

```php
new HeaderOptions(
    string $text,
    string $value,
    ?string $hide,
    ?string $description,
    ?bool $translate,
    ?bool $sortable,
    ?bool $filterable,
    ?string $align,
    ?string $customFilter,
);
```

All null properties are not included in `toArray()` method.

---

## Enum

### CollectableEnum

To extend functionality of your `BackedEnum Enum` use Trait `\Totem\SamSkeleton\App\Bundles\Enum\CollectableEnum`.
It allows you to use methods :

- `names()` - Array of all enum names
- `values()` - Array of all enum values
- `all()` - Array of items from `collection()` method
- `toArray()` - Array of enums [name => value]
- `collection()` - Converts to Laravel collection (used to `all()` method)
- `toCollection()` - Converts to Laravel collection
- `toSelect()` - Selectable array with custom description `[ ['text', 'value'] ]`

For localization create method `description` in your enum:

```php
public function description(): string
{
    return match ($this) {
        //   
    }
}
```

### ComparableEnum

To extend functionality to compares of your `BackedEnum Enum` use
Trait `\Totem\SamSkeleton\App\Bundles\Enum\ComparableEnum`.
It allows you to use methods :

- `equals()` - Checks if the current enum instance is equal to the provided enum instance.
- `not()` - Checks if the current enum instance is not equal to the provided enum instance.
- `oneOf()` - Checks if the current enum instance is one of the provided enum instances.
- `notAnyOf()` -Checks if the current enum instance is not any of the provided enum instances.

---

## EloquentModel

### EloquentDecorator

If you need to morph your class when get from other model
use `\Totem\SamSkeleton\App\Bundles\EloquetModel\EloquentDecoratorTrait`

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

### AsArrayCast

It is used to store model attributes as arrays in the database as JSON and to convert this data back to arrays on
read.
This makes it easy to work with arrays in Eloquent models, and the data is automatically saved as JSON in the
database.

### SanitizeFieldCast

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

### EloquentRelation

Adds support for relationships with json columns. hasManyThroughJson method that allows you to create `HasManyThrough`
relationships with support for JSON columns.

---

## ExceptionsHandler

Enhances Laravel's default exception handling by providing custom responses and detailed context information.
This ensures that API errors are managed effectively and that developers have the tools needed to diagnose and address
issues within their applications.

example of implementation:

```php
$this->app->bind(ExceptionHandler::class => Handler::class);
```

---

## FileHandler

### Pdf

Package extends the functionality of SnappyPdf provided by the barryvdh/laravel-snappy package. This service is a
simplified and enhanced interface for generating and testing PDF.

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

Middleware that ensures all incoming HTTExceptionsHandler requests to your Laravel application expect a JSON response.

example of use in provider:

```php
  $this->getRouter()->prependMiddlewareToGroup('api', ForceJsonMiddleware::class);
```

---

## Repository

Extend yours repository with one of base repositories:

- `\Totem\SamSkeleton\App\Bundles\Repository\BaseRepository`
- `\Totem\SamSkeleton\App\Bundles\Repository\Repository`

`Repository` class contains basic methods for getting models

- `all()` - get all models
- `allWith()` - get all models with relationships
- `findBy()` - find model by custom attribute

For better control about your repositories you can use additional interfaces

- `\Totem\SamSkeleton\App\Bundles\Repository\Contracts\RepositoryUUIDInterface` - UUID primary key
- `\Totem\SamSkeleton\App\Bundles\Repository\Contracts\RepositoryINTInterface` - INT primary key

### Activable

If your model is using `active` column add `\Totem\SamSkeleton\App\Bundles\Repository\Activable` Trait to your
repository.

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

---

## Resource

### ApiCollection

Used to return a collection of models in an API response. Extends the ResourceCollection by providing additional information
to the API response

### ApiResource

extends JsonResource

- `toArray()` method transforms the resource into an array
- `with()` method includes information about the apiVersion
- `newCollection()` Creates a new ApiCollection instance for a given resource collection.
- `whenHasAttribute()` Checks if the resource has the specified attribute.
- `noContent()` - creates an ApiResource instance with `true` as the resource value, which allows the response to be returned with an HTTP 204 No Content code.

---



## Support

### Webhook

Utility designed to handle and log webhook events in a Laravel application, particularly focusing on job events managed
by Laravel Horizon.

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

---



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

### TaskCommand

This service provides methods for setting and retrieving output messages, as well as formatting and printing
information.

---

## QueryFilter

Extend yours controller with `\Totem\SamCore\App\Controllers\ApiController`
or use `\Totem\SamCore\App\Traits\CanFilterApi` Trait to get access for [querying requests](#filtering)

#### Filtering

Filtering can be implemented as a query parameter named for the field to be filtered on.

All filtered fields must be preceded by the phrase "filter".

`GET /users?filter[active]=1`

Multiple filters result in an implicit AND.

`GET /users?filter[active]=1&filter[email]=like:%com.pl`

Search in relation field is also possible.

`GET /users?filter[role.name]=admin`

For queries that require non-simple equal comparisons, a colon and the use of one of the available comparisons must be required:

| Query                          | Description                                             |
|--------------------------------|---------------------------------------------------------|
| `?filter[field]=1`             | Equal (default)                                         |
| `?filter[field]=eq:1`          | Equal (alias)                                           |
| `?filter[field]=neq:1`         | Not Equal                                               |
| `?filter[field]=gt:7`          | Greater then                                            |
| `?filter[field]=gte:7`         | Greater or equal                                        |
| `?filter[field]=lt:7`          | Less then                                               |
| `?filter[field]=lte:7`         | Less or equal                                           |
| `?filter[field]=null`          | Equal to NULL (MySQL equivalent: IS NULL)               |
| `?filter[field]=-null`         | Equal to NULL (MySQL equivalent: IS NOT NULL)           |
| `?filter[field]=nnull`         | Alias for -null                                         |
| `?filter[field]=in:1,3,5`      | Group search for field (MySQL equivalent: WHERE IN)     |
| `?filter[field]=-in:1,3,5`     | Group search for field (MySQL equivalent: WHERE NOT IN) |
| `?filter[field]=notin:1,3,5`   | Alias for -in                                           |
| `?filter[field]=like:%com.pl`  | Search for field (MySQL equivalent: LIKE)               |
| `?filter[field]=-like:%com.pl` | Search for field (MySQL equivalent: NOT LIKE)           |
| `?filter[field]=nlike:%com.pl` | Alias for -like                                         |
| `?filter[field]=bt:7,10`       | Between (MySQL equivalent BETWEEN)                      |

#### Sorting

To run a sorted search, you must pass the `sort` parameter.

If the sort result has to be reversed, the field name should be preceded by a `minus`.

If the result is to be sorted in more than one field, the parameter should be given a list of fields separated by commas.

`GET /users?sort=lastname,-firstname`

#### Pagination

Two parameters must be used to get a specific page of the result list:

* **offset**: Specifies the number of portions of returned data and is numbered from 0.

* **limit**: Specifies the number of elements on the page.

`GET /users?limit=15&offset=0`

#### Relations

To include model relations you must add `include` parameter. To attach more relations, names should be separated by commas.

`GET /users?include=manager,department`

It is possible to include nested relations, for that use dot expression.

`GET /users?include=department.users`

---

## ValueObject

Useful in value objects or data transfer objects (DTOs) where you often need to validate and parse input data before
using it. It provides a simple and reusable way to handle common parsing scenarios.

example:

```php
$intValue = ParseValueObject::intOrNull('123'); // Returns 123
$nullValue = ParseValueObject::intOrNull(null); // Returns null
```
