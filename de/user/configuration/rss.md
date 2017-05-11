# RSS

wallabag stellt RSS Feeds für jeden Artikelstatus bereit: ungelesen,
Favoriten und Archiv.

Als erstes musst du einen persönlciehn Token erstellen: Klicke auf
`Token generieren`. Es ist möglich deinen Token zu ändern, indem du auf
`Token zurücksetzen` klickst.

Jetzt hast du drei Links, einen für jeden Status: Füge sie in deinem
liebsten Feedreader hinzu.

Du kannst auch definieren wie viele Artikel du in deinem RSS Feed
(Standardwert: 50) haben willst.

There is also a pagination available for these feeds. You can add
`?page=2` to jump to the second page. The pagination follow [the
RFC](https://tools.ietf.org/html/rfc5005#page-4) about that, which means
you'll find the `next`, `previous` & `last` page link inside the
&lt;channel&gt; tag of each RSS feed.
