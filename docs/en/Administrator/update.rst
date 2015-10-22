Update wallabag
===============

Update an existing wallabag installation
----------------------------------------

In order to update your installation, download and unzip the archive
into your installation folder. For example on Ubuntu/Debian:

::

    wget http://wllbg.org/latest
    unzip latest
    rsync -ur wallabag-version-number/* /var/www/html/wallabag/ # could be another location such as /srv/html, /usr/share/nginx/html

After that, just access wallabag in your browser and follow the
instructions to finish the update.

You can verify at the bottom of the configuration page that you’re
running the last version.

**If it fails**, just delete the ``install`` folder and clear the cache:

::

    cd /var/www/html/wallabag/
    rm -r cache/* install/

Clearing the cache is also possible in the configuration page, clicking
on the link ``Delete Cache``.
