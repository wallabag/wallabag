Android App
===========


Purpose of this document
------------------------

This document describes how you can setup your Android application to work with your Wallabag instance. There is no difference in this procedure for Wallabag v1 or v2.


Steps to configure your app
---------------------------

When you first start the app, you see the welcome screen, where you are adviced to configure the app for your Wallabag instance at first.

.. image:: ../../img/user/android_welcome_screen.de.png
    :alt: Welcome screen
    :align: center

Just confirm that message and you get redirected to the settings screen.

.. image:: ../../img/user/android_configuration_screen.de.png
    :alt: Settings screen
    :align: center

Fill in your Wallabag data. You need to enter your Wallabag address. It is important that this URL does not end with a slash. Also add your Wallabag credentials to the user name and password field.

.. image:: ../../img/user/android_configuration_filled_in.de.png
    :alt: Filled in settings
    :align: center

After you have filled in your data, push the button Connection test and wait for the test to finish. 

.. image:: ../../img/user/android_configuration_connection_test.de.png
    :alt: Connection test with your Wallabag data
    :align: center

The connection test shall finish with success. If not, you need to fix this first until you proceed. 

.. image:: ../../img/user/android_configuration_connection_test_success.de.png
    :alt: Connection test successful
    :align: center

After the connection test was successful, you can push the button to get your feed credentials. The app now tries to login to your Wallabag instance and get the user id and the corresponding token for the feeds.

.. image:: ../../img/user/android_configuration_get_feed_credentials.de.png
    :alt: Getting the feed credentials
    :align: center

When the process of getting your feed credentials finishes with success you see a toast message that the user id and the token were automatically filled in to the form.

.. image:: ../../img/user/android_configuration_feed_credentials_automatically_filled_in.de.png
    :alt: Getting feed credentials successful
    :align: center

Now you need to scroll to the bottom of the settings menu. Of course you can adjust the given settings to your needs. Finish the configuration of your app with pushing the save button.

.. image:: ../../img/user/android_configuration_scroll_bottom.de.png
    :alt: Bottom of the settings screen
    :align: center

After hitting the save button, you get the following screen. The app proposes to initiate a syncronisation process to update your feeds of articles. It is recommended to acknowledge this action and press Yes.

.. image:: ../../img/user/android_configuration_saved_feed_update.de.png
    :alt: Settings saved the first time
    :align: center

Finally after the syncronisation finished successfully, you are presented the list of unread articles. 

.. image:: ../../img/user/android_unread_feed_synced.de.png
    :alt: Filled article list cause feeds successfully syncronized
    :align: center



Known limitations
----

2FA
~~~

Currently the does not support two-factor authentication. You should disable that to get the app working.


Limited amount of articles with Wallabag v2
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In your Wallabag web instance you can configure how many items are part of the RSS feed. This option did not exist in Wallabag v1, where all articles were part of the feed. So if you set the amount of articles being displayed greater than the number of items being content of your RSS feed, you will only see the number of items in your RSS feed. 


SSL/TLS encryption
~~~~~~~~~~~~~~~~~~

If you can reach your Wallabag web instance via HTTPS, you should use that. Especially if your HTTP URL redirects you to the HTTPS one. Currently, the app cannot handle that redirect properly.


References
----------

`Source code of the Android application <https://github.com/wallabag/android-app>`_

`Android Application on F-Droid <https://f-droid.org/repository/browse/?fdfilter=wallabag&fdid=fr.gaulupeau.apps.InThePoche>`_

`Android Application on Google Play <https://play.google.com/store/apps/details?id=fr.gaulupeau.apps.InThePoche>`_

`Support chat <https://gitter.im/wallabag/wallabag>`_

