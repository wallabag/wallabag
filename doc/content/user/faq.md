---
title: FAQ
weight: 4
---

You can also check for [common errors]({{< relref "../admin/common_errors.md" >}}) regarding the installation & configuration of your instance.

## During the installation, I got the error `Error Output: sh: 1: @post-cmd: not found`

It seems you have a problem with your `composer` installation. Try to
uninstall and reinstall it.

[Read the documentation about composer to know how to install
it](https://getcomposer.org/doc/00-intro.md).


## I can't validate the registration form

Ensure that all fields are properly filled:

-   valid email address
-   same passwords in two fields


## I'm not receiving my activation email

Are you sure your email address was correct? Did you check your spam
folder?

If you still don't see the activation email, please ensure that you have
installed and properly configured a mail transfer agent. Be sure to
include a firewall rule for SMTP. E.g., if using firewalld:

    firewall-cmd --permanent --add-service=smtp
    firewall-cmd --reload

Lastly, if you have SELinux enabled, set the following rule:

`setsebool -P httpd_can_sendmail 1`


## When I click on the activation link, I've got this message: `The user with confirmation token does not exist`.

You already enabled your account or the URL of the activation email is
wrong.


## I forgot my password

You can reset your password by clicking on `Forgot your password?` link,
on the login page. Then, fill the form with your email address or your
username, you'll receive an email to reset your password.


## I've got the `failed to load external entity` error when I try to install wallabag

As described [here](https://github.com/wallabag/wallabag/issues/2529),
please edit your `web/app.php` file and add this line:
`libxml_disable_entity_loader(false);` on line 5.

This is a Doctrine / PHP bug, nothing we can do about it.


## Why is SQLite not recommended for production installations?

MySQL is the default database for standard wallabag installations. SQLite is still useful for development and small/simple setups, but it is not recommended as the main production database because database migrations can be difficult to support reliably.

For production installations, we recommend using MySQL or PostgreSQL.

