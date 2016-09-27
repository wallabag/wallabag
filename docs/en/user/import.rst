Migrate from a third service
============================

From Pocket
-----------

Create a new application on Pocket
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To import your data from Pocket, we use the Pocket API. You need to create
a new application on their developer website to continue.

* Create a new application `on the developer website <https://getpocket.com/developer/apps/new>`_
* Fill in the required fields: application name, application description,
  permissions (only **retrieve**), platform (**web**), accept the terms of service
  and submit your new application

Pocket will give you a **Consumer Key** (for example, `49961-985e4b92fe21fe4c78d682c1`).
You need to configure the ``pocket_consumer_key`` into the ``Import`` section in the ``Internal settings`` menu.

Now, all is fine to migrate from Pocket.

Import your data into wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on ``Import`` link in the menu, on ``Import contents`` in Pocket section
and then on ``Connect to Pocket and import data``.

You need to authorize wallabag to interact with your Pocket account.
Your data will be imported. Data import can be a demanding process for your server.

From Readability
----------------

Export your Readability data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the tools (`https://www.readability.com/tools/<https://www.readability.com/tools/>`_) page, click on "Export your data" in the "Data Export" section. You will received an email to download a json (which does not end with .json in fact).

Import your data into wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on ``Import`` link in the menu, on ``Import contents`` in Readability section
and then select your json file and upload it.

Your data will be imported. Data import can be a demanding process for your server.

From Instapaper
---------------

Export your Instapaper data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the settings (`https://www.instapaper.com/user<https://www.instapaper.com/user>`_) page, click on "Download .CSV file" in the "Export" section. A CSV file will be downloaded (like ``instapaper-export.csv``).

Import your data into wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Click on ``Import`` link in the menu, on ``Import contents`` in Instapaper section
and then select your CSV file and upload it.

Your data will be imported. Data import can be a demanding process for your server.

From HTML or JSON file
----------------------

*Feature not yet implemented in wallabag v2.*
