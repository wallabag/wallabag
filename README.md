# poche
Abandon Pocket, Instapaper and other Readability service : adopt poche. It is the same, but it is open source.

![poche](http://inthepoche.com/img/logo.png)

The website of poche is [inthepoche.com](http://inthepoche.com).

To get news from poche, [follow us on twitter](http://twitter.com/getpoche).

[If you want to flattr poche, it's here.](https://flattr.com/thing/1225780/nicosombpoche-on-GitHub)

## Usage
You can easily add a "poched" page with the bookmarklet.

You can :
* read a page in a comfortable reading view
* archive a link
* put a link in favorite
* delete a link

## Security
You **have** to protect your db/poche.sqlite file. Modify the virtual host of your website to add this condition :
```apache
<Files ~ "\.sqlite$">
    Order allow,deny
    Deny from all
</Files>
```

## Import from Pocket

If you want to import your Pocket datas, [export them here](https://getpocket.com/export). Put the HTML file in your poche directory, execute import.php file locally by following instructions. Be careful, the script can take a very long time.

## License
Copyright © 2010-2013 Nicolas Lœuillet <nicolas@loeuillet.org>
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.