---
title: Android
weight: 1
---

## Purpose of this document

This document describes how you can setup your Android application to
work with your wallabag instance. There is no difference in this
procedure between wallabag v1 and v2.

## Steps to configure your app

When you first start the app, you see the welcome screen, which advises
you to configure the app for your wallabag instance.

![Welcome screen](/img/user/android_welcome_screen.en.png)

Just confirm that message and you will be redirected to the settings screen.

![Settings screen](/img/user/android_configuration_screen.en.png)

Fill in your wallabag data. You need to enter your wallabag address.
**It is important that this URL does not end with a slash**. Also add
your wallabag credentials to the username and password fields.

![Filled in settings](/img/user/android_configuration_filled_in.en.png)

After you have filled in your data, press the Connection test button and
wait for the test to finish.

![Connection test with your wallabag data](/img/user/android_configuration_connection_test.en.png)

The connection test should finish successfully. If not, you need to fix
this before proceeding.

![Connection test successful](/img/user/android_configuration_connection_test_success.en.png)

After the connection test is successful, you can press the button to get
your feed credentials. The app will try to login to your wallabag
instance and get the user id and the corresponding token for the feeds.

![Getting the feed credentials](/img/user/android_configuration_get_feed_credentials.en.png)

When the process of getting your feed credentials finishes successfully,
you will see a toast message indicating that the user id and token were
automatically filled in to the form.

![Getting feed credentials successful](/img/user/android_configuration_feed_credentials_automatically_filled_in.en.png)

Now you need to scroll to the bottom of the settings menu. You can
adjust the given settings to your needs. Finish the configuration of
your app by pressing the save button.

![Bottom of the settings screen](/img/user/android_configuration_scroll_bottom.en.png)

After pressing the save button, you will see the following screen. The app
proposes to initiate a synchronization process to update your feeds of
articles. It is recommended to acknowledge this action and press Yes.

![Settings saved the first time](/img/user/android_configuration_saved_feed_update.en.png)

Finally, after the synchronization finishes successfully, you will be
presented with the list of unread articles.

![Filled article list cause feeds successfully synchronized](/img/user/android_unread_feed_synced.en.png)

## Known limitations


### Two factor authentication (2FA)

Currently, the Android application does not support two-factor
authentication. You should disable it to get the application working.

### Limited amount of articles with wallabag v2

In your wallabag web instance, you can configure how many items are part
of the RSS feed. This option did not exist in wallabag v1, where all
articles were part of the feed. So if you set the number of articles
being displayed greater than the number of items being content of your
RSS feed, you will only see the number of items in your RSS feed.

### SSL/TLS encryption

If you can reach your wallabag web instance via HTTPS, you should use
that, especially if your HTTP URL redirects you to the HTTPS one.
Currently, the app cannot handle that redirect properly.

## References


-   [Source code of the Android
    application](https://github.com/wallabag/android-app)
-   [Android Application on
    F-Droid](https://f-droid.org/repository/browse/?fdfilter=wallabag&fdid=fr.gaulupeau.apps.InThePoche)
-   [Android Application on Google
    Play](https://play.google.com/store/apps/details?id=fr.gaulupeau.apps.InThePoche)
