# Pocket

## Créer une nouvelle application dans Pocket

Pour importer vos données depuis Pocket, nous utilisons l'API de Pocket.
Vous devez créer une nouvelle application sur leur site dédié aux
développeurs pour continuer.

-   Créez une nouvelle application [sur leur site
    Développeurs](https://getpocket.com/developer/apps/new)
-   Remplissez les champs requis : nom de l'application, description de
    l'application, permissions (seulement **retrieve**), la plateforme
    (**web**), acceptez les termes d'utilisation du service et soumettez
    votre application

Pocket vous fournira une **Consumer Key** (par exemple,
49961-985e4b92fe21fe4c78d682c1). Vous devez configurer la
`pocket_consumer_key` dans le menu `Configuration`.

Maintenant, tout est bien configuré pour migrer depuis Pocket.

### Importez vos données dans wallabag

Cliquez sur le lien `Importer` dans le menu, sur `Importer les contenus`
dans la section Pocket puis sur
`Se connecter à Pocket et importer les données`.

Vous devez autoriser wallabag à se connecter à votre compte Pocket. Vos
données vont être importées. L'import de données est une action qui peut
être couteuse pour votre serveur.
