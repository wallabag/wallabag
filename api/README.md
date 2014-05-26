Wallabag API
===

Gives [Wallabag](https://www.wallabag.org/ "Wallabag") a basic API for accessing the stored links and for adding new links.

***

### Install ###

 1. Rename the Wallabag_API folder to api.
 2. Put it under the Wallabag root web folder.
 3. Open the "users.config.php" file and configure it.
	 * uid = The user's ID.
	 * key = A randum string that is the API key for authenticating an API call.
 
    $config[] = array( "uid" => 1, "key" => "RandomXYZ-APIKey" );
 4. You can add multiple users by adding another of the above lines to the config file and changing the uid to reflect the users Wallabag id.


### API Reference ###

**r = [get|change|delete|add]**

	?r=get&o=all&apikey=RandomXYZ-APIKey
	?r=change&o=fav&id=8&apikey=RandomXYZ-APIKey
	?r=delete&id=8&apikey=RandomXYZ-APIKey
	?r=add&o=fav&url=https%3A%2F%2Fgithub.com%2Ffaulker&apikey=RandomXYZ-APIKey

**get** - Get Wallabag items.

	o = [all|fav|archive]
	all - All Wallabag items.
	fav - All Wallabag items marked as favorites.
	archive - All Wallabag items that have been archived.
	
	?r=get&o=all&apikey=RandomXYZ-APIKey

**change** - Mark an item as a favorite or archive it.

	o = [fav|archive]
	fav - Mark an item as a favorite.
	archive - Archive an item.
	
	?r=change&o=fav&id=8&apikey=RandomXYZ-APIKey

 * Requires an "id" of the item being changed.

**delete** - Delete an item.

	?r=delete&id=8&apikey=RandomXYZ-APIKey

* Requires an "id" of the item being changed.

**add** - Adds an item.

	url = [encoded url string]
	
	?r=add&o=fav&url=https%3A%2F%2Fgithub.com%2Ffaulker&apikey=RandomXYZ-APIKey

***

### TO-DO List ###

* Add the ability to manage tags.
* Add a section to Wallabag's config page to manage users/API keys.


***
* http://faulk.me 
* http://github.com/faulker