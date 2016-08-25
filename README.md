[![Build Status](https://api.travis-ci.org/wallabag/wallabag.svg?branch=master)](https://travis-ci.org/wallabag/wallabag)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wallabag/wallabag/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wallabag/wallabag/?branch=master)
[![Gitter](https://badges.gitter.im/gitterHQ/gitter.svg)](https://gitter.im/wallabag/wallabag)

# What is wallabag?
wallabag is a self hostable application allowing you to not miss any content anymore.
Click, save and read it when you can. It extracts content so that you can read it when you have time.

More informations on our website: [wallabag.org](https://wallabag.org)

# Install wallabag

If you don't have it yet, please [install composer](https://getcomposer.org/download/) or be sure to use Composer 1.2 (`composer selfupdate` can help you about that). Read [our documentation](http://doc.wallabag.org) to have more information about installation. 
Then you can install wallabag by executing the following commands:

```
    git clone https://github.com/wallabag/wallabag.git
    cd wallabag
    git checkout 2.1
    ./install.sh
    php bin/console server:run --env=prod
```

## License
Copyright © 2013-2016 Nicolas Lœuillet <nicolas@loeuillet.org>
This work is free. You can redistribute it and/or modify it under the
terms of the MIT License. See the COPYING file for more details.
