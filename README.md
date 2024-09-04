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
