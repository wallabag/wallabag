Mode maintenance
================

Si vous devez effectuer de longues tâches sur votre instance de wallabag, vous pouvez activer le mode maintenance.
Plus personne ne pourra accéder à wallabag.

Activer le mode maintenance
---------------------------

Pour activer le mode maintenance, exécutez cette commande :

::

    bin/console lexik:maintenance:lock --no-interaction -e=prod

Vous pouvez spécifier votre adresse IP dans ``app/config/config.yml`` si vous souhaitez accéder à wallabag même si
 le mode maintenance est activé. Par exemple :

::

    lexik_maintenance:
        authorized:
            ips: ['127.0.0.1']


Désactiver le mode maintenance
------------------------------

Pour désactiver le mode maintenance, exécutez cette commande :

::

    bin/console lexik:maintenance:unlock -e=prod
