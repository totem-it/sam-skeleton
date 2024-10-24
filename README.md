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

- [Auth](#Auth)
    - [AuthorizedRequest](#AuthorizedRequest)
    - [TrustOnlyAuthenticated](#TrustOnlyAuthenticated)
- [Middleware](#middleware)
    - [LocalizationMiddleware](#LocalizationMiddleware)
    - [ForceJsonMiddleware](#ForceJsonMiddleware)
- [Resource](#resource)
    - [ApiCollection](#ApiCollection)
    - [ApiResource](#ApiResource)
- [ValueObject](#ValueObject)

---

## Auth

### AuthorizedRequest

The trait is used in `FormRequest` classes to automatically check if a user is authorized to perform a given action.
It ensures that only authenticated users can proceed with the request.

### TrustOnlyAuthenticated

The middleware checks if the authenticated user’s UUID matches the UUID in the route

example:

```php
Route::middleware(TrustOnlyAuthenticated::class)->group(function () {
    Route::post('/user/{uuid}/update', [UserController::class, 'update']);
```

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

This middleware changes the `accept: *` header to `accept: application/json`.

example:

```php
$this->app['router']->prependMiddlewareToGroup('api', ForceJsonMiddleware::class);
// inside service provider

Route::middleware(ForceJsonMiddleware::class)->get('/', [MyController::class, 'index']);
// inside routes
```

---

## Resource

### ApiCollection

Used to return a collection of models in an API response. Extends the ResourceCollection by providing additional information to the API response

### ApiResource

extends JsonResource

- `whenHasAttribute()` Checks if the resource has the specified attribute.
- `noContent()` - Allows the response to be returned with an HTTP 204 (No Content) status code.

---

## ValueObject

Useful in value objects (VO) or data transfer objects (DTOs) where you often need to validate and parse input data
before using it. It provides a simple and reusable way to handle common parsing scenarios.

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
