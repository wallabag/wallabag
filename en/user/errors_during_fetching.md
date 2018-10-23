# Errors while fetching articles

## Why does the fetch of an article fail?

There may be several reasons:

-   network problem
-   wallabag can't fetch content due to the website structure

## Check production log for debug / error message

By default, if a website can't be fetched because of a request error (a 404 page, a timeout, a SSL problem, etc.) the error log message will be displayed in the `WALLABAG_DIR/var/logs/prod.log` file.

If you find a line starting with `graby.ERROR` during the timeframe of your test it means the request failed because of an error.

Please report that error (and the whole text around in the log file) when opening an issue on GitHub.

## Enable log to help us identify the problem

If you can't find an error message in the log and really can't find a way to parse the content after trying the previous 2 steps, you can enable log which will help us to find why it fails.

- edit `app/config/config_prod.yml`
- replace [in line 18](https://github.com/wallabag/wallabag/blob/master/app/config/config_prod.yml#L18) `error` to `debug`
- `rm -rf var/cache/*`
- empty file `var/logs/prod.log`
- reload your wallabag and refetch the content
- paste the file `var/logs/prod.log` in a new issue on GitHub

## How can I help to fix that?

You can try to fix this problem by yourself (so we can be focused on
improving wallabag internally instead of writing siteconfig :) ).

You can try to see if it works here:
[<http://f43.me/feed/test>](http://f43.me/feed/test) (it uses almost the
same system as wallabag to retrieve content).

If it works here and not on wallabag, it means there is something
internally in wallabag that breaks the parser (hard to fix: please open
an issue about it).

If it doesn't works, try to extract a site config using:
[<http://siteconfig.fivefilters.org/>](http://siteconfig.fivefilters.org/)
(select which part of the content is actually the content). You can
[read this documentation
before](https://help.fivefilters.org/full-text-rss/site-patterns.html).

You can test it on **f43.me** website: click on **Want to try a custom
siteconfig?** and put the generated file from
siteconfig.fivefilters.org.

Repeat until you have something ok.

Then you can submit a pull request to
[<https://github.com/fivefilters/ftr-site-config>](https://github.com/fivefilters/ftr-site-config)
which is the global repo for siteconfig files.
If you can't wait for update from fivefilters - you can place your config file into directory
`vendor/j0k3r/graby-site-config` in your wallabag main directory.
Keep in mind these changes will be erased if you update your wallabag.

## How can I try to re-fetch this article?

If wallabag failed when fetching an article, you can click on the reload
button (the third on the below picture).

![Refetch content](../../img/user/refetch.png)
