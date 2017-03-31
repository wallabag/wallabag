Conseils pour développeurs front-end
====================================

Depuis la version 2.3, wallabag utilise webpack pour générer ses assets.

Mode développeur
----------------

Si le serveur fonctionne en mode dev, vous devez lancer la commande ``yarn run build:dev`` pour générer les fichiers de sortie javascript pour chaque thème. Ils sont nommés ``%theme%.dev.js`` et sont ignorés par git. Vous devez relancer la commande ``yarn run build:dev`` pour chaque changement que vous effectuez dans les fichiers assets (js, css, images, polices,...).

Live reload
-----------

Webpack apporte le support pour la fonctionnalité de live reload, ce qui signifie que vous n'avez pas besoin de regénérer manuellement le fichier de sortie javascript ni de rafraichir la page dans votre navigateur. Les changements sont appliqués automatiquement. Vous avez juste besoin de mettre le paramètre ``use_webpack_dev_server`` à ``true`` dans ``app/config/config.yml`` et de lancer ``yarn run watch`` pour que cela soit actif.

.. note::

    N'oubliez pas de remettre ``use_webpack_dev_server`` à ``false`` lorsque vous n'utilisez pas la fonctionnalité de live reload.

Production builds
-----------------

Lorsque vous committez vos changements, vous devez les compiler dans un environnement de production en exécutant ``yarn run build:prod``. Cela compilera tous les assets nécessaires pour wallabag. Pour tester que cela fonctionne proprement, vous devrez avoir un serveur en mode de production, par exemple avec ``bin/console server:run -e=prod``.

.. note::

    N'oubliez pas de générer des fichiers en mode production avant de committer !


Code style
----------

Le style de code est vérifié par deux outils : stylelint pour le (S)CSS et eslint pour le JS. La configuration ESlint config est basée sur le preset Airbnb base.
