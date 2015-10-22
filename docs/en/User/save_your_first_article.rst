.. _`Save your first article`:

Save your first article
=======================

Once connected on wallabag, you have many ways to save an article.

From the web application
------------------------

Let’s see first how to do from the web application. In the menu, you
have a link **save a link**. Clicking on it, a form shows up: you simply
have to type the web adress of the article you want to save.

Confirm to store the content of the article.

By default, only the text is saved. If you want to store a copy of the
images on your server, you have to enable the setting
*DOWNLOAD\_PICTURES*. Read the chapter on hidden options for more
information.

From the bookmarklet
--------------------

From `Wikipedia’s definition`_

    A bookmarklet is a `bookmark`_ stored in a `web browser`_ that
    contains `JavaScript`_\ commands to extend the browser’s
    functionality.

    Bookmarklets are unobtrusive scripts stored as the URL of a bookmark
    in a web browser or as a hyperlink on a web page.

    When clicked, a bookmarklet performs some function, one of a wide
    variety such as a search query or data extraction. Bookmarklets are
    usually `JavaScript programs`_.

From the wallabag’s menu, click on **settings**. On the first part of
this page, we have listed all the ways to save an article. You’ll find
the bookmarklet (it’s the ``Bag it!`` link) to drag and drop in the
bookmarks bar of your web browser. From now on, when you want to save
the article you are browsing, you just have to click on this bookmarklet
and the article will be automatically saved.

From your smartphone
--------------------

Above all else
~~~~~~~~~~~~~~

To use a smartphone application, you have to enable RSS feeds from the
settings panel of wallabag. Then some information will be displayed,
like your security token. Read the chapter on RSS feeds for more
information.

Android
~~~~~~~

Installation and configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can download the android application from the `Google Play Store`_
and from `F-droid`_. It’s the exact same application on those two
stores.

Once installed, start the application, go to the **settings** part et
fill in the **URL (complete address of your wallabag installation or
your Framabag account)** and **User ID (in most cases, you’ll have to
put 1)** fields. If you have created multiple accounts from wallabag,
you will have to to fill the user account you want to connect to your
application and your security **Token** (enter properly all the token’s
letters as seen in the settings part of wallabag).

Saving of an article
^^^^^^^^^^^^^^^^^^^^

Now that everything is correctly set up, as soon as you browse on your
smartphone’s web browser, you can share an article in wallabag at any
time from the **Share** menu: you’ll find a **Bag it!** entry which will
add your article in wallabag.

Reading
^^^^^^^

When you open the application, click on Synchronize: your recently saved
articles will be downloaded on your smartphone.

You don’t need an internet connection anymore: click on **List
articles** to start your reading.

At the end of each article, a **Mark as read** button allows you to
archive the article.

To date, the synchronisation occurs in one direction (from wallabag to
the application), thus preventing mark as read an article on wallabag
from your smartphone.

iOS
~~~

Installation and configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can download the iOS application from the `AppStore`_.

Once installed, configure the app by filling following fields inside the
settings: the **URL (complete address of your wallabag installation or
your Framabag account)** and **User ID (in most cases, you’ll have to
put 1)** field. If you have created multiple accounts from wallabag, you
will have to to fill the user account you want to connect to your
application and your security **Token** (enter properly all the token’s
letters as seen in the settings part of wallabag).

Usage
^^^^^

If the app is configured correctly, the app will automatically download
the articles from your wallabag (use **pull-to-refresh** to trigger this
update manually). Once an article is downloaded, it’ll be available
offline from your app.

Unfortunately you can only locally mark an article as read (it will not
synchronise to your online wallabag).

Saving articles
~~~~~~~~~~~~~~~

If you’re browsing a website and want to add the current article to your
wallabag, simply tap the **Share**-button and select **Bag it!** (if you
don’t find the wallabag icon, have a look in the **more**-menu). If
everything is set up correctly, your article will be saved (you may have
to login from time to time).

Windows Phone
~~~~~~~~~~~~~

Installation and configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can download the Windows Phone application from the `Windows Store`_
or directly from your smartphone’s Store.

Once installed, the application will show you a notification on the
first launch, asking the configuration of your wallabag server. Go to
the **Settings** part of the application by pressing the three dots menu
at the bottom of the screen, then fill in the **URL (complete address of
your wallabag installation or your Framabag account)** and **User ID (in
most cases, you’ll have to put 1)** fields.

If you have created multiple accounts from wallabag, you will have to to
fill the user account you want to connect to your application and your
security **Token** (enter properly all the token’s letters as seen in
the setting part of wallabag).

From your web browser
---------------------

Firefox Classic Add-on
~~~~~~~~~~~~~~~~~~~~~~

Download the Firefox add-on at `addons.mozilla.org`_ and install it like
any other Firefox add-on.

In the add-on’s settings, fill the complete URL of your installation of
wallabag or your Framabag account.

Personalize the Firefox toolbar to add wallabag (**W** icon). When you
find an article you want to save, click on this icon: a new window will
open to add the article and will close itself automatically.

Firefox Social API Service
~~~~~~~~~~~~~~~~~~~~~~~~~~

*Available from wallabag v1.9.1 only*

You will need an https connection to use this. It’s a Firefox
`requirement`_, sorry.

With Firefox 29+ versions, your browser comes with an integrated
interface to share things to multiple social services directly from your
browser. In the Firefox interface, it is shown a paper plane-like icon
that you will use to share a page, which means here, save an article.
You can add the service by going into the Config page of wallabag, then
click on Mozilla Services Social API Extension. You must also accept to
use Firefox Services.

Chrome
~~~~~~

Download the Chrome add-on `on the dedicated website`_ and install it
like any other Chrome add-on.

In the add-on’s settings, fill the complete URL of your installation of
wallabag or your Framabag account.

During the addon’s installation, a new icon appear in Chrome toolbar (a
**W** icon). When you find an article you want to save, click on this
icon: a popup will appear to confirm that your article has been saved.

Opera
~~~~~

The recent versions of Opera (15+) allow to install add-ons compatible
with Chrome.

First, install the add-on named `Download Chrome Extensions`_ which will
allow you to install add-ons from the Chrome Web Store. Then, go `to to
Google site`_ and get the Chrome add-on by clicking on *Add to Opera*. A
message will invite you to confirm this action because this add-on is
not coming from a certified source. The behavior will be the same as for
Chrome (see above).

.. _Wikipedia’s definition: http://fr.wikipedia.org/wiki/Bookmarklet
.. _bookmark: http://en.wikipedia.org/wiki/Internet_bookmark
.. _web browser: http://en.wikipedia.org/wiki/Web_browser
.. _JavaScript: http://en.wikipedia.org/wiki/JavaScript
.. _JavaScript programs: http://en.wikipedia.org/wiki/Computer_program
.. _Google Play Store: https://play.google.com/store/apps/details?id=fr.gaulupeau.apps.InThePoche
.. _F-droid: https://f-droid.org/app/fr.gaulupeau.apps.InThePoche
.. _AppStore: https://itunes.apple.com/app/id828331015
.. _Windows Store: https://www.microsoft.com/en-us/store/apps/wallabag/9nblggh11646
.. _addons.mozilla.org: https://addons.mozilla.org/firefox/addon/wallabag/
.. _requirement: https://developer.mozilla.org/en-US/docs/Mozilla/Projects/Social_API/Manifest#Manifest_Contents
.. _on the dedicated website: https://chrome.google.com/webstore/detail/wallabag/bepdcjnnkglfjehplaogpoonpffbdcdj
.. _Download Chrome Extensions: https://addons.opera.com/en/extensions/details/download-chrome-extension-9/
.. _to to Google site: https://chrome.google.com/webstore/detail/wallabag/bepdcjnnkglfjehplaogpoonpffbdcdj
