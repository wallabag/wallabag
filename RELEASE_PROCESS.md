## Definition

A release is mostly a git tag of http://github.com/wallabag/wallabag, following [semantic versioning](http://semver.org).
The last release at the time of writing is 2.0.0-alpha.2, from the v2 branch.

<<<<<<< 32a41b155703fe86a556e94145a3ad09bdf4f1bc
### Steps to release

During this documentation, we assume the release is `release-2.0.0-beta.1`.

#### Files to edit

- `app/config/config.yml` (`wallabag_core.version`)
- `README.md` (`composer create-project` command)
- `docs/en/user/installation.rst` and its translations (`composer create-project` command)

#### Create release on GitHub

- Run these commands to create the tag:

```
    git checkout v2
    git pull origin v2
    git checkout -b release-2.0.0-beta.1
    SYMFONY_ENV=prod composer up --no-dev
    git add --force composer.lock
    git add README.md
    git commit -m "Release wallabag 2.0.0-beta.1"
    git push origin release-2.0.0-beta.1
```

- Create a new pull request with this title `DON'T MERGE Release wallabag 2.0.0-beta.1`. This pull request is used to launch builds on Travis-CI.
- Run these commands to create the package:

```
    git clone git@github.com:wallabag/wallabag.git -b release-2.0.0-beta.1 release-2.0.0-beta.1
    SYMFONY_ENV=prod composer up -d=release-2.0.0-beta.1 --no-dev
    tar czf wallabag-release-2.0.0-beta.1.tar.gz --exclude="var/*" --exclude=".git" release-2.0.0-beta.1
```

- [Create the new release on GitHub](https://github.com/wallabag/wallabag/releases/new). You have to upload on this page the package.
- Delete the `release-2.0.0-beta.1` branch and close the pull request (**DO NOT MERGE IT**).
- Update the URL shortener (used on `wllbg.org` to generate links like `http://wllbg.org/latest-v2-package` or `http://wllbg.org/latest-v2`)
- Update [the downloads page](https://github.com/wallabag/wallabag.org/blob/master/content/pages/download.md) on the website (MD5 sum, release date)
- Drink a beer!
=======
### Steps
- Update `wallabag.version` is up-to-date in `app/config/config.yml` if necessary
- run composer update to make sure `composer.lock` is up-to-date
- add and update `composer.lock`: `git add -f composer.lock && git commit -m "Added composer.lock for 2.0.0-alpha.3 release"`
- create the tag: `git tag 2.0.0-alpha.3`
- remove composer.lock, and commit: `git rm composer.lock && git commit -m "Removed composer.lock"`
- push the tag: `git push origin 2.0.0-alpha.3`
- go to http://github.com/wallabag/wallabag/releases
- find the tag that was created in the list, click on the tag. Edit the release name / description
>>>>>>> Added RELEASE_PROCESS document

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
