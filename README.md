<p align="center">
    <a href="https://www.odoo.com/" target="_blank" rel="external">
        <img src="https://raw.githubusercontent.com/panlatent/yii2-odoo/master/docs/_static/logo.svg?sanitize=true" height="80px">
    </a>
    <h1 align="center">Odoo JSON-RPC Client, Query and ActiveRecord for Yii2</h1>
    <br>
<p>

This extension provides the [Odoo](https://www.odoo.com) integration for the [Yii framework 2.0](http://www.yiiframework.com/).
It includes [Web Service API](https://www.odoo.com/documentation/10.0/api_integration.html) support and also implements
the `Query` and `ActiveRecord` pattern.

Documentation is at [Read The Docs](https://yii2-odoo.panlatent.com/).

[![Build Status](https://travis-ci.org/panlatent/yii2-odoo.svg)](https://travis-ci.org/panlatent/yii2-odoo)
[![Coverage Status](https://coveralls.io/repos/github/panlatent/yii2-odoo/badge.svg)](https://coveralls.io/github/panlatent/yii2-odoo)
[![Latest Stable Version](https://poser.pugx.org/panlatent/yii2-odoo/v/stable.svg)](https://packagist.org/packages/panlatent/yii2-odoo)
[![Total Downloads](https://poser.pugx.org/panlatent/yii2-odoo/downloads.svg)](https://packagist.org/packages/panlatent/yii2-odoo) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/yii2-odoo/v/unstable.svg)](https://packagist.org/packages/panlatent/yii2-odoo)
[![License](https://poser.pugx.org/panlatent/yii2-odoo/license.svg)](https://packagist.org/packages/panlatent/yii2-odoo)

Requirements
------------
+ PHP 7.0 or higher

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist panlatent/yii2-odoo "*"
```

or add

```
"panlatent/yii2-odo": "*"
```

to the require section of your `composer.json` file.

Usage
-----

Once the extension is installed, simply use it in your code by  :

Add the component to your application.
```php
'components' => [
    'odoo' => [
        'class' => 'panlatent\odoo\Connection',
        'dsn' => 'localhost:8000/jsonrpc',
        'database' => '',
        'username' => '',
        'password' => '',
    ]
]
```

The extension support **Yii2 Debug** extension:

Add the panel component to your application.
```php
'modules' => [
    'debug' => [
        'panels' => [
            'odoo' => [
                'class' => panlatent\odoo\debug\OdooPanel::class,
            ]
        ]
    ]
]
```

The extension support **Yii2 Gii** extension:

Add the panel component to your application.
```php
'modules' => [
    'gii' => [
        'generators' => [
             \panlatent\odoo\gii\generators\model\Generator::class,
        ]
    ]
]
```

License
-------
The Yii2 Odoo is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).