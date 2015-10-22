Session issues
==============

If you end up disconnected even while checking the *Stay signed in
checkbox*, please run the following commands as root (or with sudo) :

::

    mkdir /var/lib/wallabag-sessions
    chown www-data:www-data /var/lib/wallabag-sessions

*NOTE : The www-data user and group may not exist, you may use
``chown http:http /var/lib/wallabag-sessions`` instead*

Then, using apache add:
``php_admin_value session.save_path /var/lib/wallabag-sessions`` to your
apache vhost, for instance ``wallabag-apache.conf`` Finally, restart
apache, for instance like this : ``/etc/init.d/apache2 restart``

If you’re using nginx, add
``php_admin_value[session.save_path] = /var/lib/wallabag-sessions`` in
your nginx configuration file. Then, restart nginx :
``/etc/init.d/nginx restart``

*NOTE : If you’re using systemd, you should do
``systemctl restart apache2`` (or nginx).*
