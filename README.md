[![Build Status](https://travis-ci.org/wallabag/wallabag.svg?branch=v2)](https://travis-ci.org/wallabag/wallabag)
[![Code Coverage](https://scrutinizer-ci.com/g/wallabag/wallabag/badges/coverage.png?b=v2)](https://scrutinizer-ci.com/g/wallabag/wallabag/?branch=v2)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wallabag/wallabag/badges/quality-score.png?b=v2)](https://scrutinizer-ci.com/g/wallabag/wallabag/?branch=v2)

# What is wallabag ?
wallabag is a self hostable application allowing you to not miss any content anymore.
Click, save, read it when you can. It extracts content so that you can read it when you have time.

More informations on our website: [wallabag.org](http://wallabag.org)

# Want to test the v2 ?

Keep in mind it's an **instable** branch, everything can be broken :)

```
git clone https://github.com/wallabag/wallabag.git -b v2
cd wallabag
composer install
php app/console wallabag:install
php app/console server:run
```

## License
Copyright © 2013-2015 Nicolas Lœuillet <nicolas@loeuillet.org>
This work is free. You can redistribute it and/or modify it under the
terms of the MIT License. See the COPYING file for more details.
