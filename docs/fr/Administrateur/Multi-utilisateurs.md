---
language: Français
currentMenu: multiusers
subTitle: wallabag multi-utilisateurs
---

# wallabag multi-utilisateurs

## Créer un nouveau compte

### Mode administrateur

Si vous souhaitez utiliser wallabag pour plusieurs personnes, il est possible de créer de nouveaux comptes depuis la page de configuration.

En bas de cette page se trouve un formulaire où vous devez saisir un nom d'utilisateur et un mot de passe.

Il est maintenant possible de se connecter avec ce compte depuis la page d'identification de wallabag.

Aucune information n'est partagée entre les différents comptes.

### Mode libre

A partir de la version 1.9, l'administrateur peut laisser libre la création de nouveaux comptes. Il doit pour cela l'autoriser en modifiant les lignes suivantes dans le fichier de configuration :

    // registration
    @define ('ALLOW_REGISTER', FALSE);
    @define ('SEND_CONFIRMATION_EMAIL', FALSE);

Ensuite, l'utilisateur rentrera lui-même son nom d'utilisateur et son mot de passe pour se créer un compte. Selon la configuration, un courriel de confirmation peut être envoyé aux utilisateurs ayant fourni une adresse électronique.

## Supprimer un compte

Il est possible de supprimer son propre compte, depuis la page de configuration. Il suffit de saisir son mot de passe et de demander la suppression.

Bien évidemment, lorsqu'il ne reste plus qu'un seul compte, il est impossible de le supprimer.
