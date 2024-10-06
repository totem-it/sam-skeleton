<p align="center"><img src="art/logo.svg" width="400" alt=""></p>

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

Functionalities are organized into packages within the src/Bundles folder:

- [Middleware](#middleware)
    - [LocalizationMiddleware](#LocalizationMiddleware)
    - [ForceJsonMiddleware](#ForceJsonMiddleware)
- [Resource](#resource)
    - [ApiCollection](#ApiCollection)
    - [ApiResource](#ApiResource)
- [ValueObject](#ValueObject)

---

## Middleware

### LocalizationMiddleware

By Default SAM-skeleton uses localization from `.env APP_LOCALE` key. To change response of API, add
header `Accept-Language`

example:

```php
Route::middleware(LocalizationMiddleware::class)->get('/', [MyController::class, 'index']);
```

### ForceJsonMiddleware

Middleware that ensures all incoming HTTExceptionsHandler requests to your Laravel application expect a JSON response.

example of use in provider:

```php
  $this->getRouter()->prependMiddlewareToGroup('api', ForceJsonMiddleware::class);
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


## ValueObject

Useful in value objects (VO) or data transfer objects (DTOs) where you often need to validate and parse input data
before
using it. It provides a simple and reusable way to handle common parsing scenarios.

Parse the property to a trimmed string or returns null.

```php
ParseValueObject::trimOrNull(' some text ');
// `some text`

ParseValueObject::trimOrNull(null); 
// null
```

Parse the property to an int or returns null.

```php
ParseValueObject::intOrNull('123'); 
// 123

ParseValueObject::intOrNull(null); 
// null
```
