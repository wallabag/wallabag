# poche
Abandon Pocket, Instapaper and other Readability service : adopt poche. It is the same, but it is open source.

To get news from poche, [follow us on twitter](http://twitter.com/getpoche).

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

## License
Copyright © 2010-2013 Nicolas Lœuillet <nicolas@loeuillet.org>
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.