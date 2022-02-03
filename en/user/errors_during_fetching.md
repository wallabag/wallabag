# Errors while fetching articles

## Why does the fetch of an article fail?

There may be several reasons:

- a network problem;
- an issue with the remote server;
- wallabag can't fetch content due to the website structure.

If wallabag doesn't succeed to fetch an article on the first try, you might want to try again by clicking on _Re-fetch content_ in the lateral bar. (One must note that this button is also useful to re-fetch content when trying new site configs, see below.)

![Refetch content](../../img/user/refetch.png)

## Check production log for error messages

By default, if a website can't be fetched because of a request error (a page not found, a timeout, a SSL problem...), the error log message will be displayed in `WALLABAG_DIR/var/logs/prod.log`.

If you find a line starting with `graby.ERROR` during the timeframe of your test, it means the request failed because of an error. The status code of the error can already give you a hint of the issue:

- a `404` code means that wallabag couldn't find the article at the given address;
- a `403` code means that access to the page is forbidden (either because of a misconfiguration of the remote server or because the host has taken anti-scrapping measures);
- a `500` code might indicate an issue with the remote server or your internet connection;
- `504` or `408` codes could indicate a time-out in the connection with the server.

If you already tried to re-fetch the content or to solve potential networking issues, please report that error (and the whole text around in the log file) by opening an issue on [GitHub](https://github.com/wallabag/wallabag/issues).

## Content is not what I wanted and/or is incomplete

Wallabag uses two systems working together to try to fetch the content of an article:

- site configuration files written for each specific domain (often called _site config_, stored in `vendor/j0k3r/graby-site-config`);
- [php-readability](https://github.com/j0k3r/php-readability), which automatically analyzes the content of a web page to determine what is more likely to be the desired content.

None of these two elements are flawless and we sometimes have to help wallabag a bit! In order to help you efficiently, we will need you to gather some information beforehand, as described below. When required, you could also create (or update) the site configuration file of the website hosting the desired content.

### Check with **f43.me** if the issue is reproducible

The very first thing is to check whether [the website **f43.me**](http://f43.me/feed/test) is able to parse the content or not. The underlying technology of this website is shared with wallabag, so they should have the same flaws. If it works on **f43.me** and not with wallabag, something is breaking wallabag's parser. It is difficult to solve and you should directly [create an issue ticket on Github](https://github.com/wallabag/wallabag/issues/new?assignees=&labels=Site+Config&template=1-fetching-content.md&title=Wrong+display+in+wallabag+%28HOST%29).

If you are self-hosting an instance of wallabag, you can join to the ticket detailed logs that will be useful to identify the origin of the issue (see below).

### Enabling debug logs (self-hosting)

If the production logs (`var/log/prod.log`) are not helpful, you might want to enable the debugging logs for wallabag's content parser, [graby](https://github.com/j0k3r/graby). These logs are also helpful when you are writing a new site config.

- edit the `monolog` part in `app/config/config_prod.yml`:
```yaml
monolog:
    handlers:
        main:
            type: fingers_crossed
            action_level: error
            handler: nested
            channels: ['!graby']
        nested:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
        graby:
            type: stream
            path: "%kernel.logs_dir%/graby.log"
            level: info
            channels: ['graby']
        console:
            type: console
```
- empty the cache with the command `rm -rf var/cache/*`;
- reload wallabag in your browser and re-fetch the content.

More detailed logs will then be available in `var/logs/graby.log`, with most of the steps taken by graby to try and fetch your article. If you can't solve the issue with these logs, paste the file `var/logs/graby.log` in [a new issue on GitHub](https://github.com/wallabag/wallabag/issues/new).

{% hint style="tip" %} It is possible to have **extremely** detailed logs on the modifications made by graby while fetching, parsing and cleaning the HTML code of one article, using `level: debug` instead of `level: info` in the `graby:` section above.

It is very useful when writing site configuration file (see below); one must note however that all the HTML code is stored in all its intermediary states and that the log file will grow very rapidly. Use with caution :){% endhint %}

### Creation/update of a site configuration file

Most of the time, issues while fetching the content of an article do not rise from a server error, but from wallabag's parser not being able to determine what is what in a webpage. This can lead to an article missing its title, the body of the article not fetched, missing paragraphs, and so on.

You can try to fix this problem by creating or updating a site configuration file yourself, so we can be focused on improving wallabag internally instead of writing these files :)! Many (many) examples are available on [fivefilters/ftr-site-config](https://github.com/fivefilters/ftr-site-config), the repository from which wallabag is pulling the configuration files.

#### Basic site configuration file

For an article hosted at `https://www.newswebsite.com/xxx/my-article.html`, wallabag will look for the site config `newswebsite.com.txt` in `vendor/j0k3r/graby-site-config`. A basic site configuration file will look like the following:
```
# Article's title
title: [XPath]

# Article's main content
body: [XPath]

# Parts to strip in the content
strip: [XPath]

# A test URL, e.g. the article you used to write the file
test_url: https://www.newswebite.com/xxx/my-article.html
```

The `[XPath]` are the specific paths leading to the desired content in the HTML page. Wallabag will be able to follow these paths in order to directly fetch the content, instead of trying to analyze what is what in the page.

You can find the _XPath_ with [this tool](https://siteconfig.fivefilters.org/): load the content by entering the URL of the article, then select the part(s) of interest in the page. The _XPath_ will be displayed at the bottom of the page. You can also look directly at the source code of the website (`Ctrl`+`U` and/or `F12` on most recent browsers) and determine the _XPath_ with the rules described in the following part.

Other elements can be specified in the site configuration files (date, authors, stripped elements...), you can check the full extent of the features in [the documentation](https://help.fivefilters.org/full-text-rss/site-patterns.html#pattern-format).

There is two ways to test and troubleshoot your new site configuration file:

- you can save the file in `vendor/j0k3r/graby-site-config`, then re-fetch the content like explained above;
- you can test the file on [*f43.me*](https://f43.me/feed/test), clicking on _Want to try a custom siteconfig?_, pasting the content of your file, and clicking on _Test_. Note that you will have additional information in the tab _Debug_.

When you are happy with your site configuration file, you can create a pull request on the main repository [fivefilters/ftr-site-config](https://github.com/fivefilters/ftr-site-config). (Note: even if you are not familiar with git, it is possible to [create or edit files on Github directly from your browser](https://help.github.com/articles/editing-files-in-another-user-s-repository/)). While your modifications are accepted in this repository, then pulled into wallabag, you can keep your file in `vendor/j0k3r/graby-site-config` (the modifications will be deleted if you update wallabag, though).

#### Basics of XPath 1.0

_XPath_ (for _XML Path Language_) is a standardized way to precisely describe the path to an element in a _XML_ document, and consequently in a webpage. These paths are mainly determined by the parent/children relationships of HTML markups/nodes (`<div></div>`, `<a></a>`, `<p></p>`, `<section></section>`...) and their attributes (`class`, `id`, `src`, `href`...). The norm itself is quite unreadable, but [this part](https://www.w3.org/TR/1999/REC-xpath-19991116/#path-abbrev) is a good overview of what is possible with this "language".

Some examples being probably more meaningful, we will create a site configuration file for the following webpage:
```html
<html>
    <head>
        <!-- metadata -->
    </head>
    <body>
        <div class="header">
            <header>
                <h1>The website name</h1>
            </header>
        </div>
        <div itemprop="articleBody">
            <article>
                <h1>My article's title</h1>
                <p class="author">by John Doe</p>
                <section>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

                    <p><strong>Read also: </strong><a href="http://...">Link to another article</a></p>

                    <div class="ads spam">One unwanted advertisement.</div>
                </section>
                <section>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                </section>
            </article>
        </div>
        <div id="footer">
            <footer>
                <!-- Many ads -->
            </footer>
        </div>
    </body>
</html>
```

##### Selection of the title

The document's title is in the first-level header `<h1>[...]</h1>`. It full _XPath_ is `/html/body/div[2]/article/h1`: starting from the root element `/`, one needs to enter `html`, then `body`, then the second `div`, then `article`, to finally arrive to `h1`. (One must note that the `header` also contains a `h1` corresponding to the website title.) However, these full _XPath_ are too complex to be useful in pages with a lot of intricate elements, so shortcuts can be used.

The first is `//` that allows to ignore an undefined number of nodes before or between elements:
* `//h1` will select both the title of the website and the title of the article;
* `//div//h1` will also select both titles (`article` and `header` nodes being ignored between the `div` and the `h1`);
* `//article/h1` will select only the `h1` directly embedded in `article`, so the title of the article!

When possible, it is recommended to use nodes attributes to identify elements. For example, we can here differentiate the `h1` by the fact that one is contained in a `div` with a `class="header"` attribute and the other in a `div` with the `itemprop="articleBody"` attribute:
* `//div[@class="header"]//h1` will select the name of the website;
* `//div[@itemprop="articleBody"]//h1` will select the article's title.

_Note: in this very simple case, the name of the attributes are different and it would be enough to use `//div[@itemprop]//h1` without specifying the value of the `itemprop` attribute. However, this is less restrictive and could lead to issues in a complex document._

Our site configuration file could eventually contain `title: //div[@itemprop="articleBody"]//h1` or `title: //article/h1`.

##### Selection of the main content and stripping of undesired elements

Selection of the body is here very easy, all the content being inside the `article` node. We can consequently put `body: //article` or `body://div[@itemprop="articleBody"]`.

We would like, however, to strip the node containing advertisements `<div class="ads spam">[...]</div>`, and the _Read also..._ link. Fortunately, it is possible to eliminate such content with site configuration files using the `strip: [XPath]` instruction!

Unfortunately, _XPath_ doesn't allow selection with a partial value of an attribute: paths `//div[@class="ads"]` or `//div[@class="spam"]` would not select the advertisement block!
We could use the `contains()` function to look for a string in the value of the `class` attribute: `//div[contains(@class, "ads")]` or `//div[contains(@class, "spam")]` would work to select the desired `div`. This solution is however far from ideal, as it would also select `div` with classes equals to `pads`, `mads` or `adslkjflkj`... To precisely select a node with an attribute (here `class`) made of a space-separated list, we shall use the following barbaric expression:

**`//div[contains(concat(' ', normalize-space(@class), ' '), ' string_to_search ')]`**

Eventually, the site config file could contain indifferently:
```
strip: //div[contains(concat(' ', normalize-space(@class), ' '), ' ads ')]
strip: //div[contains(concat(' ', normalize-space(@class), ' '), ' spam ')]

# Note that this also work!
strip: //div[@class="ads spam"]
```

Finally, we would like to delete the internal links of the website to allow for an easy reading in wallabag. We would like to select the following paragraph:
```html
<p><strong>Read also: </strong><a href="http://...">Link to another article</a></p>
```

It is not possible here to select the paragraph node with an attribute and we cannot delete every `strong` and `a` (links) of the document! Fortunately, _XPath_ allows for the selection of a node with one or many specific children, with the notation `//node[child]`.

_Note: one must not confuse `//node[@attribute]` (e.g. `//div[@class="..."]`) and `//node[child]` (e.g. `//p[strong]`). Likewise, one must not mistake `//p/strong`, which selects the `strong` node and its content if it's in a `p`, for `//p[strong]`, which selects a paragraph having at least one `strong` node in it._

As many paragraphs can have either a link or a `strong` node in it, we will restrain the path using the `and` operator: `//p[strong and a]` allows for the selection of a paragraph having both elements in it. To restrain even more the selection, we can look into the content of a node with `contains()`.

The configuration file could eventually contain:
```
strip: //p[contains(strong, 'Read') and a]
```

#### Useful links

More information on _XPath_ is available in the text of the norm, particularly the [part on the abbreviated syntax](https://www.w3.org/TR/1999/REC-xpath-19991116/#path-abbrev) which summarize a good number of shortcuts. The website **devhints.io** also have a [very complete cheat-sheet on _XPath_](https://devhints.io/xpath).

Finally, if you would like to dynamically test an _XPath_ on an existing code, you can use [this sandbox](http://www.whitebeam.org/library/guide/TechNotes/xpathtestbed.rhtm), where you can upload an XML/HTML code.
