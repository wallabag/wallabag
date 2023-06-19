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

- [Create the new release on GitHub](https://github.com/wallabag/wallabag/releases/new) by targetting the `master` branch or any appropriate branch (for instance backports). 
- Update [website](https://github.com/wallabag/website) to change the redirect rule for `/latest-v2-package` & `/latest-v2`. They both should redirect to the asset of the GitHub release (this asset will be uploaded to the release thanks to [a GitHub action](https://github.com/wallabag/wallabag/blob/master/.github/workflows/upload-release-package.yml)). 
- Update Dockerfile https://github.com/wallabag/docker (and create a new tag)
- Update [website](https://github.com/wallabag/website) website (downloads, MD5 sum, releases and new blog post)
- Put the next patch version suffixed with `-dev` in `app/config/wallabag.yml` (`wallabag_core.version`)
- Drink a :beer:!

### Target PHP version
`composer.lock` is _always_ built for a particular version, by default the one it is generated (with `composer update`).

If the PHP version used to generate the .lock isn't a widely available one (like PHP 8), a more common one should
be locally specified in `composer.lock`:

```json
    "config": {
        "platform": {
            "php": "7.4.29",
            "ext-something": "4.0"
        }
    }
```
