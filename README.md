Self-Processing Forms
=====================
*A set of PHP forms that process themselves. One file, ready to `<? include ?>`.*

---------------------------------------------------------------------------

**Original Author**: [@NicholasRBowers](http://twitter.com/NicholasRBowers)

---------------------------------------------------------------------------

Objective
---------
To create self-processing, PHP-driven forms for websites (and quite possibly, WordPress plugins based on the same logic).  These forms contain the configuration setup, the HTML/PHP rendering of the forms, and the logic to process themselves once user data is submitted.

Reasoning
---------
It's much more manageable, when building a website, to `<?php include(contact-form.php) ?>` one holistic PHP form that processes itself, instead of having to maintain the front-end and back-end files separately.  All changes are made in one place.

This is a set of one-file forms driven by inclusive PHP that actually processes itself.  That is, it is it's own back-end file.  The idea is that when the file is requested, it checks to see if there is any `_POST` data - if there isn't, it displays a certain, default message and/or form.  When the user hits the submit button, the form references itself using the `PHP_SELF` call.  When `_POST` data exists, it processes the data and renders conditional parts of the web page depending on whether or not the data processing functions were successful (email, adding information to databases, etc.).

The user stays on the current page, which makes for a better user experience.  Additionally, a web-developer can hide content on the success page that customers can't just link to - they actually have to go through the form to access it.  This project also only relies on PHP technology, meaning that it's completely ran on the web-server.  Customers using NoScript or have JavaScript disabled can still experience your contact form without impediment.

Overview
--------
* Contact Form
* Login Form - *Coming Soon!*
* Registration Form - *Coming Soon!*

Features
--------
* Sanitizes user input values to protect from hacker attacks.
* Utilizes PHP technology to self-process user information.
* Does not use any JavaScript for validation or otherwise.
* Remembers the user's recent information via cookies, and populates applicable fields.  

*Contact Form*
* Sends HTML-enabled E-mail.
* Stores user information in a configured MySQL database.
* Fulfills email standards (RFC 2822, etc.).
* Built-in validation of user-provided form data (`_POST` data).  

*Login Form*
* Works with encrypted MySQL databases populated with user/pass pairs.

Security Consideration
----------------------
Some web-developers put their back-end PHP files outside of the web-root.  This is not necessary if the PHP form doesn't handle or contain any sensitive/encrypted information. If sensitive information must be handled, place this information in a different PHP file outside of the web-root, and include the contents within the contact-form.php file.  All user data that comes from forms are sanitized before processing, to ensure that users can't escape the PHP, and force an error to occur, thus displaying some of the back-end PHP code in their browser.

Changelog
---------
**Recent changes**

*Contact Form*
* Restructured file to make configuration more clear.
* Made script more adaptable, able to handle any number of forms.
* Able to specify which form fields are required when declared.
* Dynamic form construction based on configured forms within the script.
* Code cleanup.
* Exhaustive commenting.

*Login Form*
* Nothing yet.

**Development released October 17, 2012**
* Development started - working towards stable release.