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
composer up
```

- Then continue with these commands:

```
git add composer.lock
git commit -m "Release wallabag $LAST_WALLABAG_RELEASE"
git push origin release-$LAST_WALLABAG_RELEASE
```

- Create a new pull request with this title `Release wallabag $LAST_WALLABAG_RELEASE`. This pull request is used to launch builds on Travis-CI.
- Once PR is green, merge it and delete the branch.
- Run this command to create the package:

```
make release VERSION=$LAST_WALLABAG_RELEASE
```

- [Create the new release on GitHub](https://github.com/wallabag/wallabag/releases/new) by targetting the `master` branch or any appropriate branch (for instance backports). You have to upload the package (generated previously).
- Update the URL shortener (used on `wllbg.org` to update links like `https://wllbg.org/latest-v2-package` or `http://wllbg.org/latest-v2`)
- Update Dockerfile https://github.com/wallabag/docker (and create a new tag)
- Update wallabag.org website (downloads, MD5 sum, releases and new blog post)
- Put the next patch version suffixed with `-dev` in `app/config/wallabag.yml` (`wallabag_core.version`)
- Drink a :beer:!

### Target PHP version
`composer.lock` is _always_ built for a particular version, by default the one it is generated (with `composer update`).

If the PHP version used to generate the .lock isn't a widely available one (like PHP 8), a more common one should
be locally specified in `composer.lock`:

```json
    "config": {
        "platform": {
            "php": "7.1.3",
            "ext-something": "4.0"
        }
    }
```
