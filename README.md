# About the class

Instagram PHP API class enables to play with Instagram API easily.

## Requirements

- PHP 5
- cURL extension
- Instagram configurations

## Let start to use

To use the class , you have to have [Instagram Developer Account](http://instagr.am/developer/register/)

After your registration process, [Register new Client](http://instagram.com/developer/clients/register/)

You will have `client_id` and `client_secret` , if you success.

## Implementation

```php
<?php

    require_once 'class.instagram.php';

    $scopes = array('basic','relationships','comments','likes');
    $actions = array('follow');

    $instagram = new Instagram(array(
        'clientId'          => 'YOUR_CLIENT_ID',
        'clientSecret'   	=> 'YOUR_CLIENT_SECRET',
        'callbackUrl'       => 'CALLBACK_URL',
        'scopes'			=> $scopes,
        'actions'			=> $actions,
    ));

echo "<a href='{$instagram->getLoginUrl()}'>Login with Instagram</a>";


?>
```

## Available Methods

- `getUserInfo()`
- `getFollowers()`
- `getFollowing()`
- `getLoginUrl()`

Methods summary will be added..