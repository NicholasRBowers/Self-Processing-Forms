Self-Aware-PHP-Form
===================
Original Author: **@NicholasRBowers**


*A PHP contact form that processes itself.  That's right, one file.*

**OBJECTIVE**:  To create a self-referential PHP-driven contact form for websites (and quite possibly, a WordPress plugin based on the same logic).

**REASONING**:  It's much nicer when building a website to `<?php include(contact-form.php) ?>` one wholistic PHP form that processes itself, instead of having to worry about both the front and backend of the form.  All changes are made in one place.

This is a one-page contact form driven by inclusive PHP that actually processes itself.  That is, it is it's own backend file.  The idea is that  when the file is requested, it checks to see if there is any POST data - if there isn't, it displays a certain, default message and/or form.  When the user hits the submit button, the form references itself using the PHP_SELF call.  When POST data exists, it processes the data and renders conditional parts of the webpage depending on whether or not the email function was successful.

The user stays on the current page, which makes for a much better user experience.  Additionally, you can hide content on the success page that people can't just link to - they actually have to go through your contact box to get through it.  This is also great because there is no JavaScript required, it's completely PHP which is ran server-side.  So people using NoScript or have JavaScript disabled can still experience your contact us form without impediment.

**SECURITY CONSIDERATION**:  Some people put their backend PHP files outside of the webroot.  This is not necessary if the PHP form doesn't handle any sensitive/encrypted information.  Plus, it's open source, and on the web, so it's not like they can't look at the base files anyways.