Hidden options
==============

Caution
-------

**Be careful**, this section is destined to advanced users. We are going
to modify an important wallabag configuration file,
``inc/poche/config.inc.php``. It is therefore advised to do a backup of
this file before you proceed. **Any error occuring during the
modification of a wallabag file could lead to malfunctions**.

This file is created when you install wallabag. Install wallabag, do a
backup copy of the file, then open it in your favorite text editor.

In this file, there are some options that are not, as of now, available
in the **config** page of wallabag.

Modification of advanced options
--------------------------------

Each option is defined this way:

::

    @define ('OPTION_NAME', 'Value');

For each line, you can only modify the ``Value`` field.

Here is the list of each option you can change:

-  ``HTTP_PORT`` (default: ``80``) : the HTTP port of your web server.
   You may need to change it if your server is behind a proxy. Accepted
   values: number
-  ``SSL_PORT`` (default: ``443``) : the HTTP port of your web server.
   You may need to change it if your server use SSLH. Accepted values:
   number
-  ``MODE_DEMO`` (default : ``FALSE)``: If you ever wanted to set up a
   demonstration server… Accepted values: ``TRUE`` or ``FALSE``.
-  ``DEBUG_POCHE`` (default: ``FALSE``) : if you encounter some problems
   with wallabag, we may ask you to active Debug mode. Accepted values:
   ``TRUE`` or ``FALSE``. Check the logs in cache/log.txt after
   activating that.
-  ``ERROR_REPORTING`` (default : ``E_ALL & ~E_NOTICE``) : Set to
   ``E_ALL`` if needed to look for eventual PHP errors.
-  ``DOWNLOAD_PICTURES`` (default: ``FALSE``) : Allows wallabag to fetch
   images from the articles you save on your server, instead of fetching
   only the text. We prefer to let you activate this option yourself.
   Accepted values: ``TRUE`` or ``FALSE``.
-  ``REGENERATE_PICTURES_QUALITY`` (default : ``75``) : In order to
   avoid security problems, pictures are regenerated if you activate the
   download of pictures. This is the percentage of quality at which they
   are saved. Increase that numbler if you want better quality, lower if
   you need better performances.
-  ``SHARE_TWITTER`` (default: ``TRUE``) : enables Twitter sharing.
   Accepted values: ``TRUE`` or ``FALSE``.
-  ``SHARE_MAIL`` (default: ``TRUE``) : enables mail sharing. Accepted
   values: ``TRUE`` or ``FALSE``.
-  ``SHARE_EVERNOTE``\ (default : ``FALSE``) : enables sharing with your
   Evernote account. Accepted values: ``TRUE`` or ``FALSE``.
-  ``SHARE_DIASPORA`` (default : ``FALSE``) : enables to share an
   article on your Diaspora account.
-  ``DIASPORA_URL`` (default : ``http://diasporapod.com``) : The URL of
   your Diaspora\* pod
-  ``CARROT`` (default : ``FALSE``) : Like Flattr, it’s a service to
   give small amounts of money to a web page. See http://carrot.org/
-  ``SHARE_SHAARLI`` (default: ``FALSE``) : enables sharing via your
   Shaarli installation (Shaarli is an open-source bookmark manager).
   Accepted values: ``TRUE`` or ``FALSE``.
-  ``SHAARLI_URL`` (default: ``'http://myshaarliurl.com'``) : defines
   your Shaarli installation URL. Accepted values: an URL.
-  ``FLATTR`` (default: ``TRUE``) : enables the possibility to Flattr an
   article (`Flattr is a microdonation platform`_). If an article is
   Flattr-able, an icon will be displayed, allowing you to send a
   microdonation to the author. Accepted values: ``TRUE`` or ``FALSE``.
-  ``SHOW_PRINTLINK`` (default: ``'1'``) : enables the Print button for
   articles. Accepted values: ``'1'`` to enable or ``'0'`` to disable.
-  ``SHOW_READPERCENT`` (default: ``'1'``) : enables the reading
   progress on articles (working on the ``default``, ``dark``,
   ``dmagenta``, ``solarized``, ``solarized-dark`` themes). Accepted
   values: ``'1'`` to enable or ``'0'`` to disable.
-  ``PAGINATION`` (default: ``'12'``) : defines the number of articles
   that are displayed on a list. Accepted values: number.

.. _Flattr is a microdonation platform: http://en.wikipedia.org/wiki/Flattr
