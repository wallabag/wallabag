Tagging-Regeln
--------------

Wenn du automatisch einen Tag zu einem neuen Artikel zuweisen lassen
möchtest, ist dieser Teil der Konfiguration, was du suchst.

### Was ist mit Tagging-Regeln gemeint?

Dies sind Regeln, die von wallabag genutzt werden, um neue Artikel
automatisch zu taggen Jedes Mal, wenn ein neuer Artikel hinzugefügt
wird, werden alle Tagging-Regeln genutzt, um deine konfigurierten Tags
hinzuzufügen, folglich um dir den Aufwand zu sparen, die Artikel manuell
einzuteilen.

### Wie benutze ich sie?

Nehmen wir an, du möchtest neuen Artikeln einen Tag *schnell gelesen*,
wenn du die Lesezeit kleiner als 3 Minuten ist. In diesem Fall solltest
du in das Regelfeld "readingTime &lt;= 3" eintragen und *schnell
gelesen* in das Tags-Feld. Mehrere Tags können gleichzeitig hinzugefügt
werden, wenn man sie mit einem Komma trennt: *schnell gelesen,
Pflichtlektüre*. Komplexe Regeln können mit vordefinierten Operatoren
geschrieben werden: Wenn *readingTime &gt;= 5 AND domainName =
"github.com"*, dann tagge als *lange zu lesen, github*.

### Welche Variablen und Operatoren kann ich zum Regeln schreiben nutzen?

Die folgenden Variablen und Operatoren können genutzt werden, um
Tagging-Regeln zu erstellen (sei vorsichtig, denn bei einigen Werten
musst du Anführungszeichen hinzufügen, z.B. `language = "de"`):


  Variable      | Bedeutung                                          
  ------------- | -------------------
  title         | Titel des Artikels
  url           | URL des Artikels
  isArchived    | Ob der Artikel archiviert ist oder nicht
  isStarred     | Ob der Artikel favorisiert ist oder nicht
  content       | Inhalt des Eintrags
  language      | Sprache des Eintrags
  mimetype      | MIME-Typ des Eintrags
  readingTime   | Die geschätzte Lesezeit in Minuten
  domainName    | Der Domain-Name des Eintrags


  Operator     | Bedeutung
  -------------| -------------
  &lt;=        | Kleiner gleich als…
  &lt;         | Kleiner als…
  =&gt;        | Größer gleich als…
  &gt;         | Größer als…
  =            | Gleich zu…
  !=           | Nicht gleich zu…
  OR           | Eine Regel oder die andere
  AND          | Eine Regel und die andere
  matches      | Testet, dass ein Feld einer Suche (unabhängig von Groß- und Kleinschreibung) übereinstimmt. Z.B.: title matches "Fußball"
  notmatches   | Testet, dass ein Feld einer Suche (unabhängig von Groß- und Kleinschreibung) nicht übereinstimmt. Z.B.: title nicht matches "Fußball"
