# Mon compte

Vous pouvez ici modifier votre nom, votre adresse email et activer la
`Double authentification`.

Si l'instance de wallabag compte plus d'un utilisateur actif, vous
pouvez supprimer ici votre compte. **Attention, nous supprimons toutes
vos données**.

## Double authentification (2FA)

> L'authentification à deux facteurs (également appelée 2FA) est une
> technologie brevetée en 1984 qui fournit l'identification des
> utilisateurs au moyen de la combinaison de deux composants différents
> .
>
> <https://fr.wikipedia.org/wiki/Authentification_forte>

**Attention**: l'activation de la 2FA depuis l'interface de
configuration n'est possible que si elle a au préalable été autorisée
dans app/config/parameters.yml en passant la propriété *twofactor\_auth*
à true (n'oubliez pas d'exécuter php bin/console cache:clear --env=prod
après modification).

Si vous activez 2FA, à chaque tentative de connexion à wallabag, vous
recevrez un code par email. Vous devez renseigner ce code dans le
formulaire suivant :

![Authentification à deux facteurs](../../../img/user/2FA_form.png)

Si vous ne souhaitez pas recevoir un code à chaque fois que vous vous
connectez, vous pouvez cocher la case
`Je suis sur un ordinateur de confiance` : wallabag se souviendra de
vous pour 15 jours.
