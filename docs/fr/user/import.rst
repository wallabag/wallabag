Migrer depuis un service externe
================================

Depuis Pocket
-------------

Créer une nouvelle application dans Pocket
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Pour importer vos données depuis Pocket, nous utilisons l'API de Pocket.
Vous devez créer une nouvelle application sur leur site dédié aux développeurs pour continuer.

* Créez une nouvelle application `sur leur site Développeurs <https://getpocket.com/developer/apps/new>`_
* Remplissez les champs requis : nom de l'application, description de l'application,
  permissions (seulement **retrieve**), la plateforme (**web**), acceptez les
  termes d'utilisation du service et soumettez votre application

Pocket vous fournira une **Consumer Key** (par exemple, `49961-985e4b92fe21fe4c78d682c1`).
Vous devez configurer la ``pocket_consumer_key`` dans la section ``Import`` du menu ``Configuration interne``.

Maintenant, tout est bien configuré pour migrer depuis Pocket.

Importez vos données dans wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliquez sur le lien ``Importer`` dans le menu, sur  ``Importer les contenus`` dans
la section Pocket puis sur ``Se connecter à Pocket et importer les données``.

Vous devez autoriser wallabag à se connecter à votre compte Pocket.
Vos données vont être importées. L'import de données est une action qui peut être couteuse
pour votre serveur (nous devons encore travailler pour améliorer cet import).

Depuis Readability
------------------

Exportez vos données de Readability
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sur la page des outils (`https://www.readability.com/tools/<https://www.readability.com/tools/>`_), cliquez sur "Export your data" dans la section "Data Export". Vous allez recevoir un email avec un lien pour télécharger le json.

Importez vos données dans wallabag 2.x
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cliquez sur le lien ``Importer`` dans le menu, sur  ``Importer les contenus`` dans
la section Readability et ensuite sélectionnez votre fichier json pour l'uploader.

Vos données vont être importées. L'import de données est une action qui peut être couteuse pour votre serveur (nous devons encore travailler pour améliorer cet import).

Depuis Instapaper
-----------------

*Fonctionnalité pas encore implémentée dans wallabag v2.*


Depuis un fichier HTML ou JSON
------------------------------

*Fonctionnalité pas encore implémentée dans wallabag v2.*
