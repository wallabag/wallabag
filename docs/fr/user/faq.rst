Foire Aux Questions
===================

Durant l'installation, je rencontre cette erreur ``Error Output: sh: 1: @post-cmd: not found``
----------------------------------------------------------------------------------------------

Il semblerait que vous ayiez un problème avec votre installation de ``composer``. Essayez de le désinstaller puis de le réinstaller.

`Vous pouvez lire la documentation de composer pour savoir comment l'installer
<https://getcomposer.org/doc/00-intro.md>`__.

Je ne peux pas valider le formulaire de création de compte
----------------------------------------------------------

Soyez sur d'avoir bien renseigné tous les champs :

* une adresse email valide
* le même mot de passe dans les deux champs

Je n'ai pas reçu mon email d'activation
---------------------------------------

Êtes-vous sur d'avoir renseigné votre bonne adresse ? Avez-vous vérifié le dossier de spams ?

Quand je clique sur le lien d'activation, j'ai ce message : ``The user with confirmation token "DtrOPfbQeVkWf6N" does not exist``.
----------------------------------------------------------------------------------------------------------------------------------

Vous avez déjà activé votre compte ou l'URL d'activation n'est pas correcte.

J'ai oublié mon mot de passe
----------------------------

Vous pouvez réinitialiser votre mot de passe en cliquant sur ``Mot de passe oublié ?``,
sur la page de connexion. Ensuite, renseignez votre adresse email ou votre nom d'utilisateur,
un email vous sera envoyé.

J'ai l'erreur ``failed to load external entity`` quand j'essaie d'installer wallabag
------------------------------------------------------------------------------------

Comme décrit `ici <https://github.com/wallabag/wallabag/issues/2529>`_, modifiez le fichier ``web/app.php`` et ajoutez la ligne ``libxml_disable_entity_loader(false);`` à la ligne 5.

C'est un bug lié à PHP et Doctrine, rien que nous ne puissions faire de notre côté.
