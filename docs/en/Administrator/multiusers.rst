.. _`Multi users`:

Multi users
===========

Create a new account
--------------------

Administrator mode
------------------

If you want to use wallabag with several persons, you can create new
accounts from the configuration page.

At the bottom of this page there is a form where you should input a user
name and a password.

It is now possible to login to this account from the login page of
wallabag.

No information are shared among the accounts.

Open registration mode
----------------------

Starting from version 1.9, the administrator can let users register by
themselves. This is done by changing the following lines in the
configuration file:

::

    // registration
    @define ('ALLOW_REGISTER', FALSE);
    @define ('SEND_CONFIRMATION_EMAIL', FALSE);

Then, a user will be able to enter his/her user name and password to
create his/her own account. Depending on the configuration, a
confimation email can be sent to users who gave an email address.

Remove an account
-----------------

It is possible to remove your own account from the configuration page.
You simply have to enter your password and to ask for the removal.

Of course, when there is only one account, it is impossible to remove
it.
