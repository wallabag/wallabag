Frequently Asked Questions
==========================

During the installation, I got the error ``Error Output: sh: 1: @post-cmd: not found``
--------------------------------------------------------------------------------------

It seems you have a problem with your ``composer`` installation. Try to uninstall and reinstall it.

`Read the documentation about composer to know how to install it
<https://getcomposer.org/doc/00-intro.md>`__.

I can't validate the registration form
--------------------------------------

Ensure that all fields are properly filled:

* valid email address
* same passwords in two fields

I'm not receiving my activation email
-------------------------------------

Are you sure your email address was correct? Did you check your spam folder?

If you still don't see the activation email, please ensure that you have
installed and properly configured a mail transfer agent. Be sure to include a
firewall rule for SMTP. E.g., if using firewalld:

::

    firewall-cmd --permanent --add-service=smtp
    firewall-cmd --reload

Lastly, if you have SELinux enabled, set the following rule:

``setsebool -P httpd_can_sendmail 1``

When I click on the activation link, I've got this message: ``The user with confirmation token "DtrOPfbQeVkWf6N" does not exist``.
----------------------------------------------------------------------------------------------------------------------------------

You already enabled your account or the URL of the activation email is wrong.

I forgot my password
--------------------

You can reset your password by clicking on ``Forgot your password?`` link,
on the login page. Then, fill the form with your email address or your username,
you'll receive an email to reset your password.
