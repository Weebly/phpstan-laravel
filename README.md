# phpstan-laravel
Laravel plugins for [PHPStan](https://github.com/phpstan/phpstan)

[![Build Status](https://img.shields.io/travis/Weebly/phpstan-laravel/master.svg?style=flat-square)](https://travis-ci.org/Weebly/phpstan-laravel)

## Usage

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev phpstan/phpstan
composer require --dev weebly/phpstan-laravel
```
Add config in composer.json for my repository
```
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/Ttdnts/phpstan-laravel.git",
        "reference":"master"
    }
],
```
And include extension.neon in your project's PHPStan config:
phpstan.neon
```
includes:
  - vendor/weebly/phpstan-laravel/extension.neon
```
Execute
```
vendor/bin/phpstan analyse -l 7 -c phpstan.neon app
```
