# README #

UW Auth module for Drupal 8 using Drupal AuthenticationServiceProvider implementation. 

This module requires Apache with mod_shib installed and was designed to work with shibd feeding mod_shib. This has only been tested under linux environments (though it could work under windows if something is feeding environment variables to Apache.

### Requirements ###

* Drupal 8
* Apache + mod_shib
* shibd (or other shibboleth daemon feeding mod_shib)
* htaccess or apache config change to "turn on" shibboleth


### Features ###

* Configuration options for email and username attributes to use from mod_shib exposed through apache as environment variables to PHP (uwnetid and eppn by default)
* Configuration option for optional auto creation of users who successfully authenticate via shib (off by default)
* Configuration option for Login path (incase you’re not using the default shib paths).
* Login link block (specific login link for shibboleth logins).
* Works in tandem with standard local auth


### How do I get set up? ###

* Clone the repo into your modules directory and use the d8-stable branch
* Install the module in Drupal 
* Configuration will appear under System (be sure to give yourself permission)
* htaccess or Apache config changes (see below)


### htaccess or Apache config ###

in htaccess add this block:
AuthType Shibboleth
ShibRequireSession Off
ShibUseHeaders On
Require shibboleth

Recommended: Alternatively add this to your apache site config instead. This ensures the change will persist through Drupal upgrades.


### Want to contribute? ###

* Fork us and send pull requests
* Altenratively email me nickwest@uw.edu and we can chat