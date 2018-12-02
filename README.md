<p align="center">
    <a href="https://www.odoo.com" target="_blank" rel="external">
        <img src="./docs/_static/logo.svg" height="80px">
    </a>
<p>

Yii2 Odoo Extension
===================
[![Build Status](https://travis-ci.org/panlatent/yii2-odoo.svg)](https://travis-ci.org/panlatent/yii2-odoo)
[![Coverage Status](https://coveralls.io/repos/github/panlatent/yii2-odoo/badge.svg)](https://coveralls.io/github/panlatent/yii2-odoo)
[![Latest Stable Version](https://poser.pugx.org/panlatent/yii2-odoo/v/stable.svg)](https://packagist.org/packages/panlatent/yii2-odoo)
[![Total Downloads](https://poser.pugx.org/panlatent/yii2-odoo/downloads.svg)](https://packagist.org/packages/panlatent/yii2-odoo) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/yii2-odoo/v/unstable.svg)](https://packagist.org/packages/panlatent/yii2-odoo)
[![License](https://poser.pugx.org/panlatent/yii2-odoo/license.svg)](https://packagist.org/packages/panlatent/yii2-odoo)

Odoo JSON-RPC Client, API, Query and ActiveRecord for Yii2.

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

License
-------
The Yii2 Odoo is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).