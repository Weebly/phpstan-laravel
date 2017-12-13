# phpstan-laravel
Laravel plugins for [PHPStan](https://github.com/phpstan/phpstan)

[![Build Status](https://img.shields.io/travis/Weebly/phpstan-laravel/master.svg?style=flat-square)](https://travis-ci.org/Weebly/phpstan-laravel)

## Usage

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev weebly/phpstan-laravel
```

And include extension.neon in your project's PHPStan config:

```
includes:
  - vendor/weebly/phpstan-laravel/extension.neon
```
