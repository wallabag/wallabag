[![Build Status](https://travis-ci.org/wallabag/wallabag.svg?branch=v2)](https://travis-ci.org/wallabag/wallabag)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wallabag/wallabag/badges/quality-score.png?b=v2)](https://scrutinizer-ci.com/g/wallabag/wallabag/?branch=v2)
[![Gitter](https://badges.gitter.im/gitterHQ/gitter.svg)](https://gitter.im/wallabag/wallabag)

# What is wallabag?
wallabag is a self hostable application allowing you to not miss any content anymore.
Click, save and read it when you can. It extracts content so that you can read it when you have time.

More informations on our website: [wallabag.org](https://wallabag.org)

# Want to test the v2?
Keep in mind it's an **unstable** branch, everything can be broken :)

If you don't have it yet, please [install composer](https://getcomposer.org/download/).
Then you can install wallabag by executing the following commands:

```
SYMFONY_ENV=prod composer create-project wallabag/wallabag wallabag "2.0.0-beta.2" --no-dev --keep-vcs
php bin/console wallabag:install --env=prod
php bin/console server:run --env=prod
```

## License
Copyright © 2013-2016 Nicolas Lœuillet <nicolas@loeuillet.org>
This work is free. You can redistribute it and/or modify it under the
terms of the MIT License. See the COPYING file for more details.
