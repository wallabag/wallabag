---
language: Français
currentMenu: upgrade
subTitle: Mettre à jour wallabag
---

# Mettre à jour wallabag

 Pour mettre à jour votre installation, téléchargez et décompressez l’archive dans votre installation (ici `/var/www/html/wallabag/`) :

    wget http://wllbg.org/latest
    unzip latest
    rsync -ur wallabag-version-number/* /var/www/html/wallabag/

Supprimez le répertoire `install` et videz le cache :

    cd /var/www/html/wallabag/
    rm -r cache/* install/

Pour vider le cache, il est également possible d'aller dans la page de configuration et de cliquer sur le lien pour supprimer le cache.

Vérifiez dans le bas de la page de configuration que la dernière version est bien installée.
