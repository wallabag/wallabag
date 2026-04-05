---
title: Import
weight: 6
---

In wallabag 2.x, you can import data from:

-   [Pocket]({{< relref "Pocket.md" >}})
-   [Instapaper]({{< relref "Instapaper.md" >}})
-   [Readability]({{< relref "Readability.md" >}})
-   [Pinboard]({{< relref "Pinboard.md" >}})
-   [elCurator]({{< relref "Elcurator.md" >}})
-   [wallabag 1.x]({{< relref "wallabagv1.md" >}})
-   [wallabag 2.x]({{< relref "wallabagv2.md" >}})

We also developed [a script to execute migrations via command-line
interface](#import-via-command-line-interface-cli).

Because imports can take ages, we developed an asynchronous tasks
system. [You can read the documentation here]({{< relref "../../admin/asynchronous.md" >}})
(for experts).

## Import via command-line interface (CLI)
---------------------------------------

If you have a CLI access on your web server, you can execute this
command to import your wallabag v1 export:

```bash
php bin/console wallabag:import username ~/Downloads/wallabag-export-1-2016-04-05.json --env=prod
```

Please replace values:

-   `username` is the user's username
-   `~/Downloads/wallabag-export-1-2016-04-05.json` is the path of your
    wallabag v1 export

If you want to mark all these entries as read, you can add the
`--markAsRead` option.

To import a wallabag v2 file, you need to add the option
`--importer=v2`.

If you want to pass the user id of the user instead of it's username,
add the option `--useUserId=true`.

You'll have this in return:

```
Start : 05-04-2016 11:36:07 ---
403 imported
0 already saved
End : 05-04-2016 11:36:09 ---
```
