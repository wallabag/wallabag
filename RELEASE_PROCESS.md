## Definition

A release is mostly a git tag of http://github.com/wallabag/wallabag, following [semantic versioning](http://semver.org).

### Steps to release

During this documentation, we assume the release is `$LAST_WALLABAG_RELEASE` (like 2.3.4).

#### Prepare the release

- Update these files with new information
    - `app/config/wallabag.yml` (`wallabag.version`)
    - `CHANGELOG.md`
- Create a PR named "Prepare $LAST_WALLABAG_RELEASE release".
- Wait for test to be ok, merge it.

#### Create a new release on GitHub

- [Create the new release on GitHub](https://github.com/wallabag/wallabag/releases/new) by targeting the `master` branch or any appropriate branch (for instance backports).
- Create the release blog post on the [website](https://github.com/wallabag/website) (based on the changelog). The release workflow rebuilds the website, which updates the download section and `releases.json`.
- For a release from the `2.6` branch, its workflow neither signs the package nor rebuilds the website: trigger a deploy of the website on Netlify, so that the download section shows the new version.
- Update Dockerfile https://github.com/wallabag/docker (and create a new tag)
- Put the next patch version suffixed with `-dev` in `app/config/wallabag.yml` (`wallabag.version`)
- Drink a :beer:!

### Package signature

The release workflow signs the package with [minisign](https://jedisct1.github.io/minisign/) and uploads `wallabag-$LAST_WALLABAG_RELEASE.tar.gz.minisig` next to it. The package is built in one job, then signed and checked in another job on a fresh runner, the only one with the `MINISIGN_SECRET_KEY` secret (stored without password). If the signing job fails after the package is published, rerun the failed jobs only: the signing job checks that the published package is the one built by the run, then signs it and replaces the signature. If they differ, it stops: delete the published package from the release, then rerun all jobs. Only releases from `master` (2.7 and later) are signed, the `2.6` branch has its own workflow without this step. Anyone can check a package with the public key:

```bash
minisign -V -P RWQT4s5qtqdNSaZOi9peL3i+pP/KMMzpk/c9XJd84ZIpxFWaGqYOlwy+ -m wallabag-$LAST_WALLABAG_RELEASE.tar.gz
```

### Target PHP version
`composer.lock` is _always_ built for a particular version, by default the one it is generated (with `composer update`).

If the PHP version used to generate the .lock isn't a widely available one (like latest PHP versions), a more common one should
be locally specified in `composer.lock`:

```json
    "config": {
        "platform": {
            "php": "8.2.27",
            "ext-something": "4.0"
        }
    }
```
