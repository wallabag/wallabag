Errors during fetching articles
===============================

Why does the fetch of an article fail?
--------------------------------------

There may be several reasons:

- network problem
- wallabag can't fetch content due to the website structure

How can I help to fix that?
---------------------------

You can try to fix this problem by yourself (so we can be focused on improving wallabag internally instead of writing siteconfig :) ).

You can try to see if it works here: `http://f43.me/feed/test <http://f43.me/feed/test>`_ (it uses almost the same system as wallabag to retrieve content).

If it works here and not on wallabag, it means there is something internally in wallabag that breaks the parser (hard to fix: please open an issue about it).

If it doesn't works, try to extract a site config using: `http://siteconfig.fivefilters.org/ <http://siteconfig.fivefilters.org/>`_ (select which part of the content is actually the content). You can `read this documentation before <http://help.fivefilters.org/customer/en/portal/articles/223153-site-patterns>`_.

You can test it on **f43.me** website: click on **Want to try a custom siteconfig?** and put the generated file from siteconfig.fivefilters.org.

Repeat until you have something ok.

Then you can submit a pull request to `https://github.com/fivefilters/ftr-site-config <https://github.com/fivefilters/ftr-site-config>`_ which is the global repo for siteconfig files.

How can I try to re-fetch this article?
---------------------------------------

If wallabag failed when fetching an article, you can click on the reload button
(the third on the below picture).

.. image:: ../../img/user/refetch.png
   :alt: Refetch content
   :align: center
