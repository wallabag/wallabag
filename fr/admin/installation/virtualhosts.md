# Virtual hosts

{% hint style="tip" %}
Nous supposons que wallabag a été installé dans le répertoire `/var/www/wallabag`.
{% endhint %}


{% hint style="danger" %}
Les exemples de configuration ci-dessous sont fournis en supposant que wallabag sera accessible à la racine d'un domaine `domain.tld` (ou d'un sous-domaine `wallabag.domain.tld`).
S'il est possible d'accéder à wallabag dans un sous-dossier (p.ex. `domain.tld/wallabag`), cette option n'est pas préconisée ni supportée par les développeurs.
{% endhint %}

## Configuration avec Apache

### Apache et _mod-php_

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

{% hint style="info" %}
N'oubliez pas d'activer le mod *rewrite* d'Apache :
`a2enmod rewrite && systemctl reload apache2`
{% endhint %}

Pour Apache 2.4, dans la section `<Directory /var/www/wallabag/web>`, vous devez remplacer les directives suivantes :

```apache
AllowOverride None
Order Allow,Deny
Allow from All
```

par

```apache
Require all granted
```

Après que vous ayez rechargé/redémarré Apache, vous devriez pouvoir avoir accès à wallabag à l'adresse <http://domain.tld>.

### Apache et PHP-FPM

Si vous utilisez PHP-FPM (via `mod_proxy_fcgi` ou similaire), il faut indiquer à Apache de *conserver* l'en-tête `Authorization` dans les requêtes pour que l'API fonctionne. Dans les versions d'Apache `>= 2.4.13` ajoutez dans la section `<Directory /var/www/wallabag/web>` :

```apache
CGIPassAuth On
```

Dans les versions d'Apache `< 2.4.13`, il faut créer une variable d'environnement pour le processus CGI. Par exemple, avec `mod_proxy_fcgi`, on peut ajouter à côté de la définition du proxy :

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

### Configuration avec Nginx

```nginx
server {
    server_name domain.tld www.domain.tld;
    root /var/www/wallabag/web;

    location / {
        # try to serve file directly, fallback to app.php
        try_files $uri /app.php$is_args$args;
    }
    location ~ ^/app\.php(/|$) {
        # Si vous utilisez PHP 5 remplacez
        # /run/php/php7.0 par /var/run/php5
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

Après que vous ayez rechargé/redémarré Nginx, vous devriez pouvoir avoir
accès à wallabag à l'adresse <http://domain.tld>.

Si vous voulez importer un fichier important dans wallabag, vous devez
ajouter cette ligne dans votre configuration nginx
`client_max_body_size XM; # allows file uploads up to X megabytes`.

## Configuration avec lighttpd

Éditez votre fichier `lighttpd.conf` collez-y cette configuration :

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
    "^/([^?])(?:\?(.))?" => "/app.php?$1&$2",
    "^/([^?]*)" => "/app.php?=$1",
)
```

## Configuration avec Caddy


Voici un caddyfile pour wallabag :

```caddy
domain.tld {
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

Vous pouvez aussi ajouter une directive `push` pour http/2 et aussi `gzip` pour compression. Le caddyfile est testé avec caddy `v0.10.4`.
