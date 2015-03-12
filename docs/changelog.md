# Changelog #

*These logs may be outdated or incomplete.*

## 3.0.0 ##

* Updated to PHP 5.3
* Fixed Composer issues

## 2.1 ##

* Added Composer support
* Replaced errors with exceptions
* Refactored to use strict equality

## 2.0 ##

* Renamed the class to Resession from Session and merged the two (Not backwards compatible)
* Added constants for security and removed multibyte dependency
* Removed the singleton pattern and unnecessary code
* Removed redirect() and setcookie()

## 1.9 ##

* Added configuration options for security level, session name and session cookies
* Added a config() method to set the options
* Added a security level of high, medium (default) and low
* If security is set to high, will check your browser agent for spoofing

## 1.8 ##

* Implemented security by adding a referer check, using cookies only on http and allowing for https sessions
* Rebuilt Resession::__construct() by including ini settings, session checking and browser checking
* Fixed a problem with set() and get() causing errors
* Rewrote destroy() to work with the new settings in __construct()
* Rewrote the logic in getId()

## 1.7 ##

* First initial release of Resession
