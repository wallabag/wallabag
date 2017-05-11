# RSS

wallabag propose un flux RSS pour chaque statut d'article : non lus,
favoris et lus.

Tout d'abord, vous devez vous créer un jeton personnel : cliquez sur
`Créez votre jeton`. Il est possible de regénérer votre jeton en
cliquant sur `Réinitialisez votre jeton`.

Vous avez maintenant trois liens, un par statut : ajoutez-les dans votre
agrégateur de flux RSS préféré.

Vous pouvez aussi définir combien d'articles vous souhaitez dans vos
flux RSS (50 est la valeur par défaut).

Une pagination est aussi disponible pour ces flux. Il suffit de rajouter
`?page=2` pour aller à la seconde page, par exemple. Cette pagination
suit [la RFC](https://tools.ietf.org/html/rfc5005#page-4), ce qui
signifie que vous trouverez la page suivante (`next`), précédente
(`previous`) et la dernière (`last`) dans la balise &lt;channel&gt; de
chaque flux RSS.
