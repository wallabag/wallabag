---
title: RSS
weight: 2
---

wallabag provides RSS feeds for each article status: unread, starred and
archive.

Firstly, you need to create a personal token: click on
`Create your token`. It's possible to change your token by clicking on
`Reset your token`.

Now you have three links, one for each status: add them into your
favourite RSS reader.

You can also define how many articles you want in each RSS feed (default
value: 50).

There is also a pagination available for these feeds. You can add
`/2` to jump to the second page. The pagination follow [the
RFC](https://tools.ietf.org/html/rfc5005#page-4) about that, which means
you'll find the `next`, `previous` & `last` page link inside the
&lt;channel&gt; tag of each RSS feed.
