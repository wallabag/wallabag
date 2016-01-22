## Definition

A release is mostly a git tag of http://github.com/wallabag/wallabag, following [semantic versioning](http://semver.org).
The last release at the time of writing is 2.0.0-alpha.2, from the v2 branch.

### Steps
- Update `wallabag.version` is up-to-date in `app/config/config.yml` if necessary
- run composer update to make sure `composer.lock` is up-to-date
- add and update `composer.lock`: `git add -f composer.lock && git commit -m "Added composer.lock for 2.0.0-alpha.3 release"`
- create the tag: `git tag 2.0.0-alpha.3`
- remove composer.lock, and commit: `git rm composer.lock && git commit -m "Removed composer.lock"`
- push the tag: `git push origin 2.0.0-alpha.3`
- go to http://github.com/wallabag/wallabag/releases
- find the tag that was created in the list, click on the tag. Edit the release name / description

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
