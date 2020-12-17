# Comandi Console

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}


wallabag possiede alcuni comandi CLI per gestire alcuni compiti. Potete elencare tutti i comandi eseguendo `bin/console` nella cartella wallabag.

Ogni comando ha un aiuto corrispondente accessibile attraverso `bin/console help %command%`.

> Se siete in una ambiente di produzione, ricordate di aggiungere `--env=prod` ad ogni comando.

Comandi rilevanti
-----------------------

Da Symfony:

 - `assets:install`: Può essere utile se mancano gli *assets*.
 - `cache:clear`: Dovrebbe essere eseguito dopo ogni aggiornamento (incluso in make update).
 - `doctrine:migrations:status`: Mostra lo stato delle vostre migrazioni del database.
 - `fos:user:activate`: Attiva manualmente un utente.
 - `fos:user:change-password`: Cambia la password per un utente.
 - `fos:user:create`: Crea un utente.
 - `fos:user:deactivate`: Disattiva un utente (ma non lo cancella).
 - `fos:user:demote`: Rimuove un ruolo ad un utente, tipicamente i diritti di amministratore.
 - `fos:user:promote`: Aggiunge un ruolo ad un utente, tipicamente i diritti di amministratore.
 - `rabbitmq:*`: Può essere utile se usate RabbitMQ.

Specifici di wallabag:

 - `wallabag:clean-duplicates`: Rimuove tutti gli articoli duplicati per un utente o per tutti gli utenti.
 - `wallabag:export`: Esporta tutti gli articoli per un utente. Potete scegliere il percorso di output del file.
 - `wallabag:import`: Importa articoli in formati differenti in un account utente.
 - `wallabag:import:redis-worker`: Utile se usate Redis.
 - `wallabag:install`: (re)Installa wallabag.
 - `wallabag:tag:all`: Etichetta tutti gli articoli per un utente usando le sue regole di etichettatura.
 - `wallabag:user:show`: Mostra i dettagli per un utente.
 - `wallabag:user:list`: Lista tutti gli utenti esistenti.
 - `wallabag:entry:reload`: Ricarica gli articoli.

wallabag:clean-duplicates
----------------------------------

Questo comando via aiuta a pulire la vostra lista di articoli in caso vi siano duplicati.

Uso:

```
wallabag:clean-duplicates [<username>]
```

Argomenti:

 - username: Utente sul quale lavorare.


wallabag:export
---------------------

Questo comando vi aiuta ad esportare tutti gli articoli per un utente.

Uso:

```
wallabag:export <username> [<filepath>]
```

Argomenti:

 - username: Utente da cui esportare gli articoli.
 - filepath: Percorso del file esportato.


wallabag:import
---------------------

Questo comando vi aiuta ad importare articoli da un’esportazione JSON.

Uso:

```
wallabag:import [--] <username> <filepath>
```

Argomenti:

 - username: Utente sul quale lavorare.
 - filepath: Percorso del file JSON.

Opzioni:

 - `--importer=IMPORTER`: L’importatore da usare: v1, v2, instapaper, pinboard, readability, firefox o chrome [default: "v1"].
 - `--markAsRead=MARKASREAD`: Segna tutti gli articoli come già letti [default: false].
 - `--useUserId`: Usa l’id utente al posto del nome utente per trovare un account.
 - `--disableContentUpdate`: Disattiva il recupero di contenuto aggiornato da un URL.


wallabag:import:redis-worker
--------------------------------------

Questo comando vi aiuta ad avviare il worker di Redis.

Uso:

```
wallabag:import:redis-worker [--] <serviceName>
```

Argomenti:

 - serviceName: Servizio da usare: wallabag_v1, wallabag_v2, pocket, readability, pinboard, firefox, chrome o instapaper.

Opzioni:

 - `--maxIterations[=MAXITERATIONS]`: Numero di iterazioni prima di fermarsi [default: false].


wallabag:install
---------------------

Questo comando vi aiuta ad installare o reinstallare wallabag.

Uso:

```
wallabag:install
```


wallabag:tag:all
---------------------

Questo comando vi aiuta ad etichettare tutti gli articoli usando le regole di etichettatura.

Uso:

```
wallabag:tag:all <username>
```

Argomenti:
 - username: Utente sul quale lavorare.


wallabag:user:show
--------------------------

Questo comando mostra i dettagli di un utente.

Uso:

```
wallabag:user:show <username>
```

Argomenti:
 - username: Utente del quale bisogna mostrare i dettagli.

wallabag:user:list
------------------

Questo comando lista tutti gli utenti esistenti.

Uso:

```
wallabag:user:list [<search>]
```

Argomenti:
 - search: Filtra la lista con il termine di ricerca dato. La ricerca è fatta sull username, nome ed e-mail dell'utente.

Opzioni:
 - `--limit=LIMIT`: Numero massimo di utenti mostrati nella lista.


wallabag:entry:reload
---------------------

Questo comando ricarica gli articoli.

Uso:

```
wallabag:entry:reload [<username>]
```

Argomenti:
 - username: Ricarica gli articoli solo per un utente specifico.
