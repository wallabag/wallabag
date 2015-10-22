Write config files
==================

wallabag can use specific site config files to parse website articles.
These files are stored in the
```inc/3rdparty/site_config/standard`` <https://github.com/wallabag/wallabag/tree/master/inc/3rdparty/site_config/standard>`__
folder.

The format used for these files is
`XPath <http://www.w3.org/TR/xpath20/>`__. Look at some examples in the
folder.

Automatic config files generation
---------------------------------

Fivefilters has created a `very useful
tool <http://siteconfig.fivefilters.org/>`__ to create config files. You
just type in the adress of the article to work on with, and you select
the area containing the content you want.

.. figure:: https://lut.im/RNaO7gGe/l9vRnO1b
   :alt: siteconfig

   siteconfig
| You should confirm this area by trying with other articles.
| When you got the right area, just click on *Download Full-Text RSS
site config* to download your file.

Manual config file generation
-----------------------------

If Fivefilters tool doesn't work correctly, take a look at the source
(Ctrl + U on Firefox and Chromium). Search for your content and get the
``class`` or the ``id`` attribute of the area containing what you want.

Once you've got the id or class, you can write for example one or
another of these lines:

::

    body: //div[@class='myclass']
    body: //div[@id='myid']

Then, test you file. If you got the right content but you want to strip
unnecessary parts, do:

::

    strip: //div[@class='hidden']

You can look at other options for siteconfig files
`here <http://help.fivefilters.org/customer/portal/articles/223153-site-patterns>`__.
