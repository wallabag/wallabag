Configuration
=============

Maintenant que vous êtes connecté, il est temps de configurer votre compte.

Cliquez sur le menu ``Configuration``. Vous avez accès à 5 onglets :
``Paramètres``, ``RSS``, ``Mon compte``, ``Mot de passe`` and ``Règles de tag automatiques``.

Paramètres
----------

Thème
~~~~~

L'affichage de wallabag est personnalisable. C'est ici que vous choisissez le thème
que vous préférez. Vous pouvez aussi en créer un nouveau, une documentation sera
disponible pour guider. Le thème par défaut est ``Material``, c'est celui
qui est utilisé dans les captures d'écran de la documentation.

Nombre d'articles par page
~~~~~~~~~~~~~~~~~~~~~~~~~~

Vous pouvez définir le nombre d'articles affichés sur chaque page.

Vitesse de lecture
~~~~~~~~~~~~~~~~~~

wallabag calcule une durée de lecture pour chaque article. Vous pouvez définir ici, grâce à la jauge, si vous lisez plus
ou moins vite. wallabag recalculera la durée de lecture de chaque article.

Langue
~~~~~~

Vous pouvez définir la langue de l'interface de wallabag. Vous devrez vous déconnecter
pour que la nouvelle langue soit prise en compte.

RSS
---

wallabag propose un flux RSS pour chaque statut d'article : non lus, favoris et lus.

Tout d'abord, vous devez vous créer un jeton personnel : cliquez sur ``Créez votre jeton``.
Il est possible de regénérer votre jeton en cliquant sur ``Réinitialisez votre jeton``.

Vous avez maintenant trois liens, un par statut : ajoutez-les dans votre agrégateur de flux RSS préféré.

Vous pouvez aussi définir combien d'articles vous souhaitez dans vos flux RSS
(50 est la valeur par défaut).

Mon compte
----------

Vous pouvez ici modifier votre nom, votre adresse email et activer la ``Double authentification``.

Double authentification (2FA)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    L'authentification à deux facteurs (également appelée 2FA) est une technologie brevetée en 1984
    qui fournit l'identification des utilisateurs au moyen de la combinaison de deux composants différents .

https://fr.wikipedia.org/wiki/Authentification_forte

Si vous activez 2FA, à chaque tentative de connexion à wallabag, vous recevrez
un code par email. Vous devez renseigner ce code dans le formulaire suivant :

.. image:: ../../img/user/2FA_form.png
    :alt: Authentification à deux facteurs
    :align: center

Si vous ne souhaitez pas recevoir un code à chaque fois que vous vous connectez,
vous pouvez cocher la case ``Je suis sur un ordinateur de confiance`` : wallabag
se souviendra de vous pour 15 jours.

Mot de passe
------------

Vous pouvez changer de mot de passe ici (8 caractères minimum).

Règles de tag automatiques
--------------------------

Si vous voulez automatiquement assigner un tag à de nouveaux articles en fonction de
certains critères, cette partie de la configuration est pour vous.

Que veut dire « règles de tag automatiques » ?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Ce sont des règles utilisées par wallabag pour automatiquement assigner un tag
à un nouvel article.
À chaque fois que vous ajoutez un nouvel article, toutes les règles sont utilisées pour ajouter
les tags que vous avez configurés, vous épargnant ainsi la peine de classer manuellement vos articles.

Comment les utiliser ?
~~~~~~~~~~~~~~~~~~~~~~

Admettons que vous voulez ajouter comme tag *« lecture rapide »* quand le temps de lecture
d'un article est inférieur à 3 minutes.
Dans ce cas, vous devez ajouter « readingTime <= 3 » dans le champ **Règle** et *« lecture rapide »* dans le champ **Tags**.
Plusieurs tags peuvent être ajoutés en même temps en les séparant par une virgule : *« lecture rapide, à lire »*.
Des règles complexes peuvent être écrites en utilisant les opérateurs pré-définis :
if *« readingTime >= 5 AND domainName = "github.com" »* then tag as *« long reading, github »*.

Quels variables et opérateurs puis-je utiliser pour écrire mes règles ?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Les variables et opérateurs suivants peuvent être utilisés lors de la création de vos règles :

===========  ==============================================  ==========  ==========
Variable     Sens                                            Opérateur   Sens
-----------  ----------------------------------------------  ----------  ----------
title        Titre de l'article                              <=          Inférieur ou égal à …
url          URL de l'article                                <           Strictement inférieur à …
isArchived   Si l'article est archivé ou non                 =>          Supérieur ou égal à …
isStared     Si l'article est en favori ou non               >           Strictement supérieur à …
content      Le contenu de l'article                         =           Égal à …
language     La langue de l'article                          !=          Différent de …
mimetype     The type MIME de l'article                      OR          Telle règle ou telle autre règle
readingTime  Le temps de lecture de l'article, en minutes    AND         Telle règle et telle règle
domainName   Le nom de domaine de l'article                  matches     Contient telle chaîne de caractère (insensible à la casse). Exemple : title matches "football"
===========  ==============================================  ==========  ==========
