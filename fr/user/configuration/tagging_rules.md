# Règles de tag automatiques

Si vous voulez automatiquement assigner un tag à de nouveaux articles en
fonction de certains critères, cette partie de la configuration est pour
vous.

## Que veut dire « règles de tag automatiques » ?

Ce sont des règles utilisées par wallabag pour automatiquement assigner
un tag à un nouvel article. À chaque fois que vous ajoutez un nouvel
article, toutes les règles sont utilisées pour ajouter les tags que vous
avez configurés, vous épargnant ainsi la peine de classer manuellement
vos articles.

## Comment les utiliser ?

Admettons que vous voulez ajouter comme tag *« lecture rapide »* quand
le temps de lecture d'un article est inférieur à 3 minutes. Dans ce cas,
vous devez ajouter « readingTime &lt;= 3 » dans le champ **Règle** et
*« lecture rapide »* dans le champ **Tags**. Plusieurs tags peuvent être
ajoutés en même temps en les séparant par une virgule : *« lecture
rapide, à lire »*. Des règles complexes peuvent être écrites en
utilisant les opérateurs pré-définis : if *« readingTime &gt;= 5 AND
domainName = "github.com" »* then tag as *« long reading, github »*.

## Quels variables et opérateurs puis-je utiliser pour écrire mes règles ?

Les variables et opérateurs suivants peuvent être utilisés lors de la
création de vos règles (attention, pour certaines valeurs, vous devez
ajouter des guillemets, par exemple `language = "en"`) :


  Variable      | Sens                                          
  ------------- | -------------------
  title         | Titre de l'article
  url           | URL de l'article
  isArchived    | Si l'article est archivé ou non
  isStarred     | Si l'article est en favori ou non              
  content       | Le contenu de l'article                              
  language      | La langue de l'article                             
  mimetype      | The type MIME de l'article                            
  readingTime   | Le temps de lecture de l'article, en minutes   
  domainName    | Le nom de domaine de l'article


  Opérateur     | Sens
  ------------- | -------------
  &lt;=         | Inférieur ou égal à …
  &lt;          | Strictement inférieur à …
  =&gt;         | Supérieur ou égal à …
  &gt;          | Strictement supérieur à …
  =             | Égal à …
  !=            | Différent de …
  OR            | Telle règle ou telle autre règle
  AND           | Telle règle et telle règle
  matches       | Contient telle chaîne de caractère (insensible à la casse). Exemple : title matches "football"
  notmatches    | Ne contient pas telle chaîne de caractère (insensible à la casse). Exemple : title notmatches "football"
