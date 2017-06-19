# Configuring paywall access

> **[warning] Important**
>
> This is the technical part about the paywall. If you are looking for the user part, please check [that page instead](../user/articles/restricted.md).

Read [this part of the documentation](../user/errors_during_fetching.md)
to understand the configuration files, which are located under `vendor/j0k3r/graby-site-config/`. For most of the websites, this file
is already configured: the following instructions are only for the websites that are not configured yet.

Each parsing configuration file needs to be improved by adding
`requires_login`, `login_uri`, `login_username_field`,
`login_password_field` and `not_logged_in_xpath`.

Be careful, the login form must be in the page content when wallabag
loads it. It's impossible for wallabag to be authenticated on a website
where the login form is loaded after the page (by ajax for example).

`login_uri` is the action URL of the form (`action` attribute in the
form). `login_username_field` is the `name` attribute of the login
field. `login_password_field` is the `name` attribute of the password
field.

For example:

```
title://div[@id="titrage-contenu"]/h1[@class="title"]
body: //div[@class="contenu-html"]/div[@class="page-pane"]

requires_login: yes

login_uri: http://www.arretsurimages.net/forum/login.php
login_username_field: username
login_password_field: password

not_logged_in_xpath: //body[@class="not-logged-in"]
```
