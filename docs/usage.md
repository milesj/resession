# Resession #

*Documentation may be outdated or incomplete as some URLs may no longer exist.*

*Warning! This codebase is deprecated and will no longer receive support; excluding critical issues.*

A small lightweight script that can manage and manipulate Session data. Calls session_start() in memory so that no header errors are thrown, as well as stores the session id in the object.

Resession is a play on words for: Session Request + Response.

* Calls session_start() automatically in memory
* Modifies the ini settings for extra security and protection
* Uses http cookies to store session data
* Get and set data as a string or array
* Clear a session key, or destroy the session
* Get and regenerate session ids
* Redirects to another page
* Can set raw cookies
* Security levels of high, medium, and low
* Configuration settings

## Installation ##

Install by manually downloading the library or defining a [Composer dependency](http://getcomposer.org/).

```javascript
{
    "require": {
        "mjohnson/resession": "3.0.0"
    }
}
```

## Configuration ##

There are 3 configuration options which deal with your security, how cookies are used in conjunction with the session and the sessions name. To apply the settings, simply pass an array of key value pairs to the constructor. The available settings are: name, cookies, security.

```php
use mjohnson\resession\Resession;

$session = new Resession(array(
    'security' => Resession::SECURITY_HIGH,
    'cookies' => true,
    'name' => 'SessionName'
));
```

Now let me quickly explain what these different options do, but it would be best to look over the script yourself to get a good understanding of it.

* `security` - You can set your level to high, medium (default) or low. If set to high or medium, the script will enable referrer checking, force the cookies on a secure http connection and set a timeout for cookie lifetime. If set to low, none of these will be enforced.
* `cookies` - If set to true, it will force the script to use cookies to store session information instead of applying a session id to the urls.
* `name` - You can set the name of your session id, session name and cookie name. By default its named "RESESSION"

## Manipulating Data ##

### Setting Data ###

The most common use for sessions, is to set data to the session. To do this we use the `set()` method. You may set data to a stand alone index, or to an index within an array by using a dot notation. The dot notation only works for single dimensional arrays.

```php
$session->set('user', array(
    'name' => 'Miles',
    'website' => 'http://www.milesj.me/'
));

// Change the name index within the user array
$session->set('user.name', 'Miles Johnson');
```

### Retrieving Data ###

To get data from a session, you would use the `get()` method. The same concept of array dot notations applies to this method. (In the example below, I will be referencing the `$data` array above).

```php
// Get the user array
$session->get('user');

// Get the users name
$session->get('user.name'); // Miles Johnson
```

## Clearing Data ##

If for some reason you need to remove an index from the session, you can use the `clear()` method. Dot notations apply to this method, as well you can pass an array of indexes to remove.

```php
$session->clear('time');
$session->clear('user.name');

// Multiple
$session->clear(array('time', 'date', 'user.name'));
```

### Destroying the Session ###

If you need to delete all data in the session entirely, you would call the `destroy()` method.

```php
$session->destroy();
```

## Accessing the Session ID ##

If you need to access the session id that is stored in the class, you can use the `id()` method.

```php
$session_id = $session->id();
```

Expanding this further, if you need to regenerate a new session id, you would call the `regenerate()` method and the new session id will be returned. You can also pass a boolean true/false argument to clear the old session file ([more information here](http://us2.php.net/session_regenerate_id)), by default it will remove the file (true).

```php
$new_session_id = $session->regenerate();

// Do not delete old session data
$new_session_id = $session->regenerate(false);
```
