# Regole di etichettatura

Se volete assegnare un'etichetta ai nuovi articoli, questa parte della
configurazione fa per voi.

## Cosa significa « regole di etichettatura » ?

Sono regole usate da wallabag per etichettare i nuovi articoli. Ogni
volta che un nuovo articolo viene aggiunto, verranno usate tutte le
regole di etichettatura per aggiungere le etichette che avete configurato,
risparmiandovi quindi il lavoro di classificare manualmente i vostri
articoli.

## Come le uso?

Immaginiamo che vogliate etichettare un contenuto come *« lettura corta »*
quando il tempo di lettura è inferiore ai 3 minuti. In questo caso,
dovreste mettere « readingTime &lt;= 3 » nel campo **Regola** e *«
lettura corta »* nel campo **Etichette**. Molte etichette possono essere
aggiunte simultaneamente separandole con una virgola: *« lettura corta,
da leggere »*. Si possono scrivere regole complesse usando gli operatori
predefiniti: se *« readingTime &gt;= 5 AND domainName = "github.com" »*
allora etichetta come *« lettura lunga, github »*.

Quali variabili ed operatori posso usare per scrivere le regole?

I seguenti operatori e variabili possono essere usati per creare regole
di tagging (attenzione, per alcuni valori, dovete aggiungere le
virgolette, per esempio `language = "en"`):


  Variable      | Significato                                          
  ------------- | -------------------
  title         | Titolo dell'articolo
  url           | URL dell'articolo
  isArchived    | Se l'articolo è archiviato o no
  isStarred     | Se l'articolo è preferito o no
  content       | Il contenuto dell'articolo
  language      | La lingua dell'aritcolo
  mimetype      | Il mime-type dell'articolo
  readingTime   | Il tempo di lettura dell'articolo stimato
  domainName    | Il nome del dominio dell'articolo


  Operatore    | Significato
  ------------- | -------------
  &lt;=         | Minore di…
  &lt;         | Strettamente minore di…
  =&gt;        | Maggiore di…
  &gt;         | Strettamente maggiore di…
  =            | Uguale a…
  !=           | Diverso da…
  OR           | Una regola o l'altra
  AND          | Una regola e l'altra
  matches      | Vede se un soggetto corrisponde alla ricerca (indipendentemente dal maiuscolo o minuscolo). Esempio: titolo corrisponde a "football"
  notmatches   | Vede se un soggetto non corrisponde alla ricerca (indipendentemente dal maiuscolo o minuscolo). Esempio: titolo non corrisponde a "football"
