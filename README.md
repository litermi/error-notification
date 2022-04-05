# Error Notification

[![Software License][ico-license]](LICENSE.md)

## About

The `Error Notification` is a package to send notification when has an error .


## Installation

Require the `cirelramos/error-notification` package in your `composer.json` and update your dependencies:
```sh
composer require cirelramos/error-notification
```


## Configuration

set provider

```php
'providers' => [
    // ...
    Cirelramos\ErrorNotification\Providers\ServiceProvider::class,
],
```


The defaults are set in `config/error-notification.php`. Publish the config to copy the file to your own config:
```sh
php artisan vendor:publish --provider="Cirelramos\ErrorNotification\Providers\ServiceProvider"
```

> **Note:** this is necessary to you can change default config



## Usage

```php

```


## License

Released under the MIT License, see [LICENSE](LICENSE).


[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

