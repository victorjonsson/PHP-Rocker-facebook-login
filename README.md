
# PHP-Rocker - Facebook login

Install this package in your [Rocker application](https://github.com/victorjonsson/PHP-Rocker) and you will have an restful API that can authenticate users
that has logged in using their facebook identities.

*This installation walk through takes for granted that you have some prior knowledge about [composer](http://getcomposer.org)*

## 1) Install PHP-Rocker

Here you can read more about [how to get started](https://github.com/victorjonsson/PHP-Rocker#installation) with PHP-Rocker

## 2) Add the facebook login package

Add `"rocker/facebook-login" : "dev-master"` to the application requirements in *composer.json* and call `composer update` in
the console.

## 3) Edit config.php

In the file config.php you add your facebook application data and change the authentication class
to Rocker\\FacebookLogin\\Authenticator

```php
return array(
    ...

    'application.auth' => array(
        'class' => '\\Rocker\\FacebookLogin\\Authenticator',
        'mechanism' => 'facebook realm="your.website.com"'
    ),

    ...

    'facebook' => array(

        'id' => 'Facebook app id',
        'secret' => 'Facebook app secret',

        # (Optional) Comma separated string with data fields in the facebook response that
        # should saved as user meta when the user gets connected
        'connect_data' => 'birthday,locale',

        # (Optional) Comma separated string with authentication mechanisms that should be disabled
        'disabled_auth_mechanisms' => 'rc4,basic',
    )

);
```