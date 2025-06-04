# Laravel API Scaffold Command

![Status](https://img.shields.io/badge/status-active-success.svg)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](/LICENSE)
![PHP](https://img.shields.io/badge/PHP-8.1-blue.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/worksofallen/laravel-api-scaffold.svg)](https://packagist.org/packages/worksofallen/laravel-api-scaffold)
[![Downloads](https://img.shields.io/packagist/dt/worksofallen/laravel-api-scaffold.svg)](https://packagist.org/packages/worksofallen/laravel-api-scaffold)
</div>

---
A Laravel Artisan command to quickly scaffold a complete RESTful API resource, including:

- Eloquent Model with SoftDeletes & fillable setup  
- Migration file with a `name` column and soft deletes support  
- API Controller with basic CRUD operations & pagination, filtering, sorting  
- Form Request classes for validation with proper JSON error responses  

---

## Installation

Require the package via Composer:

```sh
composer require worksofallen/laravel-api-scaffold:v1.0.0 --dev
```

Publish the command if necessary (depends on how you package it).

---

## Usage

Run the custom Artisan command to generate a full API scaffold for a resource:

```sh
php artisan make:api {name}
```

Replace `{name}` with the singular model name, e.g., `Product`, `Category`, etc.

### What gets created?

- `app/Models/{Name}.php` — Model with fillable `name` attribute and soft deletes.  
- `database/migrations/xxxx_xx_xx_xxxxxx_create_{snake_plural}_table.php` — Migration with `name` and soft deletes columns.  
- `app/Http/Requests/{Name}StoreRequest.php` — Validation request for storing.  
- `app/Http/Requests/{Name}UpdateRequest.php` — Validation request for updating.  
- `app/Http/Controllers/API/{Name}Controller.php` — API controller with:  
- `index` method supporting search (`q`), sort (`sortField`, `sortOrder`), and pagination (`sizePerPage`).  
- `store`, `show`, `update`, `destroy` methods.  

---

## Features

- Standardized API scaffolding reduces repetitive boilerplate code.  
- Soft deletes enabled by default.  
- Search, sort, paginate support out of the box.  
- JSON validation error responses for API-friendly error handling.  
- Clean, maintainable, and easy to customize generated code.  

---

## Example

```sh
php artisan make:api Product
```

Generates `Product` API scaffolding:

- Model: `app/Models/Product.php`  
- Migration: `database/migrations/xxxx_xx_xx_create_products_table.php`  
- Requests: `ProductStoreRequest`, `ProductUpdateRequest`  
- Controller: `app/Http/Controllers/API/ProductController.php`  

---

## Authors <a name = "authors"></a>

- [Allen Alvarez](https://github.com/worksofallen)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Security

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
