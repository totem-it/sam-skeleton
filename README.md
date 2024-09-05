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

Functionalities are organized into packages within the src/Bundles folder:
- Middleware
    - [LocalizationMiddleware](#LocalizationMiddleware)
    - [ForceJsonMiddleware](#ForceJsonMiddleware)
- [ValueObject](#ValueObject)

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

## ValueObject

Useful in value objects (VO) or data transfer objects (DTOs) where you often need to validate and parse input data before
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
