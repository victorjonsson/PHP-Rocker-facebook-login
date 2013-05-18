
# PHP-Rocker - Facebook login

Install this package in your [Rocker application](https://github.com/victorjonsson/PHP-Rocker) and you will have a restful
API that can authenticate users that has logged in using their facebook identities.

*This installation walk through takes for granted that you have some prior knowledge about [composer](http://getcomposer.org)*

### 1) Install PHP-Rocker

Here you can read more about [how to get started](https://github.com/victorjonsson/PHP-Rocker#installation) with PHP-Rocker

### 2) Add the facebook login package

Add `"rocker/facebook-login" : "dev-master"` to the application requirements in *composer.json* and call `composer update` in
the console.

### 3) Edit config.php

In the file config.php you add your facebook application data and change the authentication class
to Rocker\\FacebookLogin\\Authenticator. You will also have to add the facebook connect operation.

```php
return array(
    ...

    'application.operations' => array(
        ...
        'facebook/connect' => '\\Rocker\\FacebookLogin\\Connect'
    ),

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


### 4) Implementation

The following example uses [rocker.js](https://github.com/victorjonsson/rocker.js) together with Facebook
javascript SDK

```html
<html>
<head></head>
<body>
    <button id="login-button">Login with FB</button>

    <script src="/scripts/rocker.js"></script>
    <script src="//connect.facebook.net/en_US/all.js"></script>

    <script>

    // Initiate FB
    FB.init({
        appId      : FB_APP_ID,
        status     : true,
        cookie     : false,
        oauth      : false
    });

    // Instantiate the Rocker server
    var rocker = new Rocker('https://api.mywebsite.com/');

    var onFacebookLogin = function() {

        // Connect user
        rocker.request({
            path : 'facebook/connect?access_token='+FB.getAccessToken(),
            method : 'POST',
            onComplete : function(status, json, http) {

                // set facebook auth data
                rocker.auth = 'facebook '+FB.getAuthResponse().userID+':'+FB.getAccessToken();

                // From here on you can access any operation that requires authentication
                rocker.me(function(rockerUser) {
                    console.log(rockerUser);
                });
            }
        });

    };

    // When user clicks on login button
    document.getElementById('login-button').onclick = function() {

        // Check login status
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                onFacebookLogin();
            } else {

                // Login
                FB.login(function(response) {
                    if(response.status == 'connected') {
                        onFacebookLogin();
                    }
                }, {scope: 'email,user_birthday,user_location'});
            }
        });
    };

    </script>
</body>
</html>
```