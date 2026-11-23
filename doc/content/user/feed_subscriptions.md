---
title: Feed subscriptions
---

Open **Feed subscriptions** in the sidebar to follow an external RSS or Atom feed. Enter a title and the address of the feed or a website that advertises one.

Manual subscriptions collect up to ten articles they have not seen on each refresh. You can save or dismiss pending articles. Clearing a feed dismisses all its pending articles. Deleting a subscription also deletes its history.

Enable automatic imports to save new articles directly in wallabag. Automatic refreshes consider the first ten valid articles offered by the feed. Items already waiting when you switch modes stay pending. The link to those articles disappears once you have processed them all.

Wallabag keeps the most recent 200 records and any articles that are still published in the feed.

Use **Refresh** to check a subscription immediately. Wallabag shows the last successful refresh and flags a feed if it has not refreshed successfully for two days.

An administrator can also schedule regular refreshes for all subscriptions with [`wallabag:fetch-rss`]({{< relref "/admin/console_commands.md" >}}).
