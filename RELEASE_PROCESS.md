## Definition

A release is mostly a git tag of http://github.com/wallabag/wallabag, following [semantic versioning](http://semver.org).

### Steps to release

During this documentation, we assume the release is `$LAST_WALLABAG_RELEASE` (like 2.3.4).

#### Prepare the release

- Update these files with new information
    - `app/config/wallabag.yml` (`wallabag_core.version`)
    - `CHANGELOG.md`
- Create a PR named "Prepare $LAST_WALLABAG_RELEASE release".
- Wait for test to be ok, merge it.

#### Create a new release on GitHub

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
-    - travis_wait bash composer install -o --no-interaction --no-progress --prefer-dist
+    - travis_wait bash composer update -o --no-interaction --no-progress --prefer-dist
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
make release VERSION=$LAST_WALLABAG_RELEASE
```

- [Create the new release on GitHub](https://github.com/wallabag/wallabag/releases/new) by targetting the `release-$LAST_WALLABAG_RELEASE` branch. You have to upload the package (generated previously).
- Close the previously created pull request (**DO NOT MERGE IT**) and delete the `release-$LAST_WALLABAG_RELEASE` branch.
- Update the URL shortener (used on `wllbg.org` to generate links like `https://wllbg.org/latest-v2-package` or `http://wllbg.org/latest-v2`)
- Update Dockerfile https://github.com/wallabag/docker (and create a new tag)
- Update wallabag.org website (downloads, MD5 sum, releases and new blog post)
- Put the next patch version suffixed with `-dev` in `app/config/wallabag.yml` (`wallabag_core.version`)
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
