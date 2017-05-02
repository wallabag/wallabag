## Definition

A release is mostly a git tag of http://github.com/wallabag/wallabag, following [semantic versioning](http://semver.org).

### Steps to release

During this documentation, we assume the release is `$LAST_WALLABAG_RELEASE`.

#### Files to edit

- `app/config/config.yml` (`wallabag_core.version`)
- `CHANGELOG.md` (by using this command `github-changes -o wallabag -r wallabag -k YOURGITHUBTOKEN --only-pulls --use-commit-body --title Changelog --date-format YYYY/MM/DD --between-tags 2.0.0-alpha.0...master -n 2.1.3`. [github-changes is available here](https://github.com/lalitkapoor/github-changes))

#### Create release on GitHub

- Run these commands to create the tag:

```
git checkout master
git pull origin master
git checkout -b release-$LAST_WALLABAG_RELEASE
SYMFONY_ENV=prod composer up --no-dev
```

- Update `.travis.yml` file and replace the composer line with this one:

```diff
script:
-    - travis_wait bash composer install -o  --no-interaction --no-progress --prefer-dist
+    - travis_wait composer update --no-interaction --no-progress
```

- Then continue with these commands:

```
git add --force composer.lock .travis.yml
git commit -m "Release wallabag $LAST_WALLABAG_RELEASE"
git push origin release-$LAST_WALLABAG_RELEASE
```

- Create a new pull request with this title `DON'T MERGE Release wallabag $LAST_WALLABAG_RELEASE`. This pull request is used to launch builds on Travis-CI.
- Run these command to create the package:

```
make release master /tmp wllbg-release prod
```

- [Create the new release on GitHub](https://github.com/wallabag/wallabag/releases/new). You have to upload on this page the package.
- Delete the `release-$LAST_WALLABAG_RELEASE` branch and close the pull request (**DO NOT MERGE IT**).
- Update the URL shortener (used on `wllbg.org` to generate links like `https://wllbg.org/latest-v2-package` or `http://wllbg.org/latest-v2`)
- Update [the downloads page](https://github.com/wallabag/wallabag.org/blob/master/content/pages/download.md) on the website (MD5 sum, release date)
- Update Dockerfile https://github.com/wallabag/docker (and create a new tag)
- Update wallabag.org website (downloads, releases and new blog post)
- Put the next patch version suffixed with `-dev` in `app/config/config.yml` (`wallabag_core.version`)
- Drink a :beer:!

### `composer.lock`
A release tag must contain a `composer.lock` file. It sets which dependencies were available at the time a release was done,
making it easier to fix issues after the release. It also speeds up `composer install` on stable versions a LOT, by skipping the
dependencies resolution part.

Since `composer.lock` is ignored by default, either it must be removed from `.gitignore` _in the release branch_,
or it must be added using `git add --force composer.lock`.

### Target PHP version
`composer.lock` is _always_ built for a particular version, by default the one it is generated (with `composer update`).

If the PHP version used to generate the .lock isn't a widely available one (like PHP 7), a more common one should
be locally specified in `composer.lock`:

```json
    "config": {
        "platform": {
            "php": "5.5.9",
            "ext-something": "4.0"
        }
    }
```
