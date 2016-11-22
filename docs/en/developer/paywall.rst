Articles behind a paywall
=========================

wallabag can fetch articles from websites which use a paywall system.

Enable paywall authentication
-----------------------------

In internal settings, in the **Article** section, enable authentication for websites with paywall (with the value 1).

Configure credentials in wallabag
---------------------------------

Edit your ``app/config/parameters.yml`` file to edit credentials for each website with paywall. Here is an example for some french websites:

.. code:: yaml

    sites_credentials:
        mediapart.fr: {username: "myMediapartLogin", password: "mypassword"}
        arretsurimages.net: {username: "myASILogin", password: "mypassword"}

.. note::

    These credentials will be shared between each user of your wallabag instance.

Parsing configuration files
---------------------------

.. note::

    Read `this part of the documentation <http://doc.wallabag.org/en/master/user/errors_during_fetching.html>`_ to understand the configuration files.

Each parsing configuration file needs to be improved by adding ``requires_login``, ``login_uri``,
``login_username_field``, ``login_password_field`` and ``not_logged_in_xpath``.

Be careful, the login form must be in the page content when wallabag loads it. It's impossible for wallabag to be authenticated
on a website where the login form is loaded after the page (by ajax for example).

``login_uri`` is the action URL of the form (``action`` attribute in the form).
``login_username_field`` is the ``name`` attribute of the login field.
``login_password_field`` is the ``name`` attribute of the password field.

For example:

.. code::

    title://div[@id="titrage-contenu"]/h1[@class="title"]
    body: //div[@class="contenu-html"]/div[@class="page-pane"]

    requires_login: yes

    login_uri: http://www.arretsurimages.net/forum/login.php
    login_username_field: username
    login_password_field: password

    not_logged_in_xpath: //body[@class="not-logged-in"]
