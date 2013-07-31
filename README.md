# poche
Abandon Pocket, Instapaper and other Readability service : adopt poche. It is the same, but it is open source.

![poche](http://inthepoche.com/img/logo.png)

The website of poche is [inthepoche.com](http://inthepoche.com).

To test poche, a demo website is online : [demo.inthepoche.com](http://demo.inthepoche.com) (login poche, password poche).

To get news from poche, [follow us on twitter](http://twitter.com/getpoche) or [read the poche blog](http://inthepoche.com/blog). A Google Group is also available : [poche-users](https://groups.google.com/forum/#!forum/poche-users).

[![flattr](http://api.flattr.com/button/flattr-badge-large.png)](http://flattr.com/thing/1265480/poche-a-read-it-later-open-source-system)

## Usage
You can easily add a "poched" page with the bookmarklet.

poche save the entire content of a poched links : text and pictures are stored on your server.

You can :
* read a page in a comfortable reading view
* archive a link
* put a link in favorite
* delete a link

## Requirements & installation
You have to install [sqlite for php](http://www.php.net/manual/en/book.sqlite.php) on your server.

Get the [latest version](https://github.com/inthepoche/poche) of poche on github. Unzip it and upload it on your server. poche must have write access on assets, cache and db directories.

That's all, **poche works** !

## Security
You **have** to protect your db/poche.sqlite file. Modify the virtual host of your website to add this condition :
```apache
<Files ~ "\.sqlite$">
    Order allow,deny
    Deny from all
</Files>
```

Nginx version:
```nginx
location ~ /(db) {
    deny all;
    return 404;
}
```

## Import from Pocket

If you want to import your Pocket datas, [export them here](https://getpocket.com/export). Put the HTML file in your poche directory, execute import.php file locally by following instructions. Be careful, the script can take a very long time.

## License
Copyright © 2010-2013 Nicolas Lœuillet <nicolas@loeuillet.org>
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
