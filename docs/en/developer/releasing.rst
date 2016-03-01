Releasing wallabag
==================

During this documentation, we assume the release is `release-2.0.0-beta.1`.

Files to edit
-------------

- ``app/config/config.yml`` (``wallabag_core.version``)
- ``README.md`` (``composer create-project`` command)
- ``docs/en/user/installation.rst`` and its translations (``composer create-project`` command)


Create release on GitHub
------------------------

- Run these commands to create the tag:

::

    git checkout v2
    git pull origin v2
    git checkout -b release-2.0.0-beta.1
    SYMFONY_ENV=prod composer up --no-dev
    git add --force composer.lock
    git add README.md
    git commit -m "Release wallabag 2.0.0-beta.1"
    git push origin release-2.0.0-beta.1


- Create a new pull request ``DON'T MERGE Release wallabag 2.0.0-beta.1``. This pull request is used to launch builds on Travis-CI.
- Run these commands to create the package:

::

    git clone git@github.com:wallabag/wallabag.git -b release-2.0.0-beta.1 release-2.0.0-beta.1
    SYMFONY_ENV=prod composer up -d=release-2.0.0-beta.1 --no-dev
    tar czf wallabag-release-2.0.0-beta.1.tar.gz --exclude="var/*" --exclude=".git" release-2.0.0-beta.1


- `Create the new release on GitHub <https://github.com/wallabag/wallabag/releases/new>`__. You have to upload on this page the package.
- Delete the ``release-2.0.0-beta.1`` branch and close the pull request (**DO NOT MERGE IT**).
- Update the URL shortener (used on ``wllbg.org`` to generate links like ``http://wllbg.org/latest-v2-package`` or ``http://wllbg.org/latest-v2``)
- Update `the downloads page <https://github.com/wallabag/wallabag.org/blob/master/content/pages/download.md>`__ on the website (MD5 sum, release date)
- Drink a beer!
