---
title: wallabag v1
weight: 7
---

If you were using wallabag v1.x, you need to export your data before
migrating to wallabag v2.x, because the application and its database
changed a lot. In your old wallabag installation, you can export your
data, which can be done on the Config page of your old wallabag
installation.

![Exporting from wallabag v1](../../../img/user/export_v1.png)

If you have multiple accounts on the same instance of wallabag, each
user must export from v1 and import into v2 its data.

If you encounter issues during the export or the import, don't hesitate
to [ask for support](https://gitter.im/wallabag/wallabag).

When you have retrieved the json file containing your entries, you can
install wallabag v2 if needed by following [the standard
procedure](../../admin/installation/).

After creating an user account on your new wallabag v2 instance, you
must head over to the Import section and select Import from wallabag v1.
Select your json file and upload it.

![Import from wallabag v1](../../../img/user/import_wallabagv1.png)
