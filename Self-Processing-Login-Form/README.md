Self-Processing Login Form
============================
*A PHP login form that processes itself. One file, ready to `<? include ?>`.*

---------------------------------------------------------------------------

**Original Author**: [@NicholasRBowers](http://twitter.com/NicholasRBowers)

---------------------------------------------------------------------------

Objective
---------
To create a self-processing PHP-driven login form for websites (and quite possibly, a WordPress plugin based on the same logic).

Reasoning
---------
It's much more manageable, when building a website, to `<?php include(login-form.php) ?>` one holistic PHP form that processes itself, instead of having to maintain the front-end and back-end files separately.  All changes are made in one place.

This is a one-file login form driven by inclusive PHP that actually processes itself.  That is, it is it's own back-end file.  The idea is that when the file is requested, it checks to see if there is any `_POST` data - if there isn't, it displays a certain, default message and/or form.  When the user hits the submit button, the form references itself using the `PHP_SELF` call.  When `_POST` data exists, it processes the data and renders conditional parts of the web page depending on whether or not the email function was successful.

The user stays on the current page, which makes for a much better experience.  This project only relies on PHP technology, meaning that it's completely ran on the web-server.  Users using NoScript or have JavaScript disabled can still experience your login form without impediment.

Overview
--------
* Utilizes PHP technology to self-process user information.
* Works with encrypted MySQL databases populated with user/pass pairs.
* Does not use any JavaScript for validation or otherwise.
* Remembers the user's last successful username via cookie.

Security Consideration
----------------------
Some web-developers put their back-end PHP files outside of the web-root.  This is not necessary if the PHP form doesn't handle or contain any sensitive/encrypted information. If sensitive information must be handled, place this information in a different PHP file outside of the web-root, and include the contents within the login-form.php file.  All user data that comes from forms are sanitized before processing, to ensure that users can't escape the PHP, and force an error to occur, thus displaying some of the back-end PHP code in their browser.

Changelog
---------
***Recent changes***
* Nothing yet.

**Development released November 13, 2012**
* Initial release