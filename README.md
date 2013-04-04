# poche

Abandon Pocket, Instapaper and other Readability service : adopt poche. It is the same, but it is open source.

## Usage

...

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