# Virtual hosts

## Configuration on Apache

Do not forget to activate the *rewrite* mod of Apache:

```bash
a2enmod rewrite && systemctl reload apache2
```

Assuming you install wallabag in the `/var/www/wallabag` folder and that
you want to use PHP as an Apache module, here's a vhost for wallabag:

```apache
<VirtualHost *:80>
    ServerName domain.tld
    ServerAlias www.domain.tld

    DocumentRoot /var/www/wallabag/web
    <Directory /var/www/wallabag/web>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app.php [QSA,L]
        </IfModule>
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeScript assets
    # <Directory /var/www/wallabag>
    #     Options FollowSymlinks
    # </Directory>

    # optionally disable the RewriteEngine for the asset directories
    # which will allow apache to simply reply with a 404 when files are
    # not found instead of passing the request into the full symfony stack
    <Directory /var/www/wallabag/web/bundles>
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
    ErrorLog /var/log/apache2/wallabag_error.log
    CustomLog /var/log/apache2/wallabag_access.log combined
</VirtualHost>
```

Note for Apache 2.4, in the section
&lt;Directory /var/www/wallabag/web&gt; you have to replace the
directives:

```apache
AllowOverride None
Order Allow,Deny
Allow from All
```

by

```apache
Require all granted
```

After reloading or restarting Apache, you should now be able to access
wallabag at <http://domain.tld>.

### PHP-FPM instead of mod_php

If you use PHP-FPM (via mod_proxy_fcgi or similar) then Apache must be
instructed to *keep* the Authorization header in requests for the API to work.
In Apache versions `>= 2.4.13` place the following in the section `<Directory
/var/www/wallabag/web>`:

```apache
CGIPassAuth On
```

In older Apache versions we have to set the header value as environment
variable for the CGI process. For example, with mod_proxy_fcgi the following
works, when placed somewhere next to the proxy definition:

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

## Configuration on Nginx

Assuming you installed wallabag in the `/var/www/wallabag` folder,
here's the recipe for wallabag :

```nginx
server {
    server_name domain.tld www.domain.tld;
    root /var/www/wallabag/web;

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /app.php$is_args$args;
    }
    location ~ ^/app\.php(/|$) {
        # if, for some reason, you are still using PHP 5,
        # then replace /run/php/php7.0 by /var/run/php5
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        # When you are using symlinks to link the document root to the
        # current version of your application, you should pass the real
        # application path instead of the path to the symlink to PHP
        # FPM.
        # Otherwise, PHP's OPcache may not properly detect changes to
        # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
        # for more information).
        fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/app.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    # return 404 for all other php files not matching the front controller
    # this prevents access to other php files you don't want to be accessible.
    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/wallabag_error.log;
    access_log /var/log/nginx/wallabag_access.log;
}
```

After reloading or restarting nginx, you should now be able to access
wallabag at <http://domain.tld>.

When you want to import large files into wallabag, you need to add this
line in your nginx configuration
`client_max_body_size XM; # allows file uploads up to X megabytes`.

## Configuration on lighttpd

Assuming you install wallabag in the `/var/www/wallabag` folder, here's
the recipe for wallabag (edit your `lighttpd.conf` file and paste this
configuration into it):

```lighttpd
server.modules = (
    "mod_fastcgi",
    "mod_access",
    "mod_alias",
    "mod_compress",
    "mod_redirect",
    "mod_rewrite",
)
server.document-root = "/var/www/wallabag/web"
server.upload-dirs = ( "/var/cache/lighttpd/uploads" )
server.errorlog = "/var/log/lighttpd/error.log"
server.pid-file = "/var/run/lighttpd.pid"
server.username = "www-data"
server.groupname = "www-data"
server.port = 80
server.follow-symlink = "enable"
index-file.names = ( "index.php", "index.html", "index.lighttpd.html")
url.access-deny = ( "~", ".inc" )
static-file.exclude-extensions = ( ".php", ".pl", ".fcgi" )
compress.cache-dir = "/var/cache/lighttpd/compress/"
compress.filetype = ( "application/javascript", "text/css", "text/html", "text/plain" )
include_shell "/usr/share/lighttpd/use-ipv6.pl " + server.port
include_shell "/usr/share/lighttpd/create-mime.assign.pl"
include_shell "/usr/share/lighttpd/include-conf-enabled.pl"
dir-listing.activate = "disable"

url.rewrite-if-not-file = (
    "^/([^?]*)(?:\?(.*))?" => "/app.php?$1&$2",
    "^/([^?]*)" => "/app.php?=$1",
)
```

## Configuration on Caddy

For caddy server configuration might be just like this (assuming wallabag installation folder is `/var/www/wallabag`):

```caddy
yourdomain.ru {
  root /var/www/wallabag/web
  fastcgi / /var/run/php7-fpm.sock php {
    index app.php
  }
  rewrite / {
    to {path} {path}/ /app.php?{query}
  }
  tls your@email.ru
  log /var/log/caddy/wbg.access.log
  errors /var/log/caddy/wbg.error.log
}
```

You can also add `push` directive for http/2 and `gzip` for compression. Tested with caddy v0.10.4
