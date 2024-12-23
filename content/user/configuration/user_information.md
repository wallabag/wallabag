---
title: User information
weight: 3
---

You can change your name, your email address and enable
`Two factor authentication`.

If the wallabag instance has more than one enabled user, you can delete
your account here. **Take care, we delete all your data**.

## Two factor authentication (2FA)

> Two-factor authentication (also known as 2FA) is a technology patented
> in 1984 that provides identification of users by means of the
> combination of two different components.
>
> <https://en.wikipedia.org/wiki/Two-factor_authentication>

**Warning**: enabling 2FA from the configuration interface is only
possible if it has been authorized before in app/config/parameters.yml
by setting the *twofactor_auth* parameter to true (do not forget to run
`php bin/console cache:clear --env=prod` after modification).

If you enable 2FA, each time you want to login to wallabag, you'll
receive a code by email. You have to put this code on the following
form.

![Two factor authentication](../../../img/user/2FA_form.png)

If you don't want to receive a code each time you want to login, you can
check the `I'm on a trusted computer` checkbox: wallabag will remember
you for 15 days.
