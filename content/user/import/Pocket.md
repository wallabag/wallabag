---
title: Pocket
weight: 1
---

## Create a new application on Pocket

To import your data from Pocket, we use the Pocket API. You need to
create a new application on their developer website to continue.

-   Create a new application [on the developer
    website](https://getpocket.com/developer/apps/new)
-   Fill in the required fields: application name, application
    description, permissions (only **retrieve**), platform (**web**),
    accept the terms of service and submit your new application

Pocket will give you a **Consumer Key** (for example,
49961-985e4b92fe21fe4c78d682c1). You need to configure the
`pocket_consumer_key` in the `Config` menu.

Now, all is fine to migrate from Pocket.

## Import your data into wallabag

Click on `Import` link in the menu, on `Import contents` in Pocket
section and then on `Connect to Pocket and import data`.

You need to authorize wallabag to interact with your Pocket account.
Your data will be imported. Data import can be a demanding process for
your server.
