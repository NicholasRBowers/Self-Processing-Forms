Self-Processing Contact Form
============================
*A PHP contact form that processes itself. One file, ready to `<? include ?>`.*

---------------------------------------------------------------------------

**Original Author**: [@NicholasRBowers](http://twitter.com/NicholasRBowers)

---------------------------------------------------------------------------

Objective
---------
To create a self-processing PHP-driven contact form for websites (and quite possibly, a WordPress plugin based on the same logic).

Reasoning
---------
It's much more manageable, when building a website, to `<?php include(contact-form.php) ?>` one holistic PHP form that processes itself, instead of having to maintain the front-end and back-end files separately.  All changes are made in one place.

This is a one-file contact form driven by inclusive PHP that actually processes itself.  That is, it is it's own back-end file.  The idea is that when the file is requested, it checks to see if there is any `_POST` data - if there isn't, it displays a certain, default message and/or form.  When the user hits the submit button, the form references itself using the `PHP_SELF` call.  When `_POST` data exists, it processes the data and renders conditional parts of the web page depending on whether or not the email function was successful.

The user stays on the current page, which makes for a much better user experience.  Additionally, a web-developer can hide content on the success page that customers can't just link to - they actually have to go through the contact form to access it.  This project also only relies on PHP technology, meaning that it's completely ran on the web-server.  Customers using NoScript or have JavaScript disabled can still experience your contact form without impediment.

Overview
--------
* Utilizes PHP technology to self-process customer information.
* Does not use any JavaScript for validation or otherwise.
* Fulfills email standards (RFC 2822, etc.).
* Built-in validation of user-provided form data (`_POST` data).

Security Consideration
----------------------
Some web-developers put their back-end PHP files outside of the web-root.  This is not necessary if the PHP form doesn't handle or contain any sensitive/encrypted information. If sensitive information must be handled, place this information in a different PHP file outside of the web-root, and include the contents within the contact-form.php file.

Changelog
---------
***Recent changes***
* Restructured file to make configuration more clear.
* Made script more adaptable, able to handle any number of forms.
* Able to specify which form fields are required when declared.
* Dynamic form construction based on configured forms within the script.
* Code cleanup.
* Exhaustive commenting.

**Development released October 17, 2012**
* Initial release