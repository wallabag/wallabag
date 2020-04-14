Installazione
-------------

{% hint style="danger" %}
Questa documentazione tradotta potrebbe non essere aggiornata. Per funzionalità o requisiti più recenti, consultare la [documentazione inglese](https://doc.wallabag.org/en/).
{% endhint %}

### Su un web server dedicato (raccomandato)

Per installare wallabag stesso dovete eseguire i seguenti comandi:

```bash
git clone https://github.com/wallabag/wallabag.git
cd wallabag && make install
```

Per attivare il server incorporato di PHP e verificare che
l’installazione sia andata a buon fine potete eseguire:

```bash
make run
```

E accedere a wallabag all’indirizzo <http://ipdeltuoserver:8000>

Per definire parametri con variabili d'ambiente, dovete impostare queste variabili con il prefisso `SYMFONY__`. Per esempio,
`SYMFONY__DATABASE_DRIVER`. Potete dare un'occhiata alla [documentazione di Symfony](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html).

### A proposito di hosting condiviso

Offriamo un pacchetto con tutte le dipendenze incluse. La configurazione
di default usa SQLite per il database. Se volete cambiare queste
impostazioni, modificate app/config/parameters.yml.

Abbiamo già creato un utente: il login e la password sono wallabag.

Con questo pacchetto, wallabag non controlla le estensioni obbligatorie usate nell'applicazione (questi controlli sono fatti durante `composer install`
quando avere un web server dedicato, vedete sopra).

Eseguite questo comando per scaricare ed estrarre il pacchetto piú
aggiornato:

```bash
wget https://wllbg.org/latest-v2-package && tar xvf latest-v2-package
```

Troverete il [hash md5 del pacchetto più aggiornato sul nostro
sito](https://static.wallabag.org/releases/).

Ora leggete la seguente documentazione per creare il vostro host
virtuale poi accedete al vostro wallabag. Se avete cambiato la
configurazione del database per usare MySQL o PostrgreSQL, dovrete
creare un utente con il comando php bin/console wallabag:install
--env=prod .

### Installazione con Docker

Offriamo un’immagine Docker per installare wallabag facilmente. Guardate
la nostra repository su [Docker
Hub](https://hub.docker.com/r/wallabag/wallabag/) per maggiori
informazioni.

Comando per avviare il container

```bash
docker pull wallabag/wallabag
```

### Installazione su Cloudron

Cloudron fornisce un modo facile di installare webapps sul vostro server
mirato ad automatizzare i compiti del sysadmin ed a mantenere le app
aggiornate. wallabag è pacchettizzata come app Cloudron ed è disponibile
all'installazione direttamente dallo store.

[Installa wallabag sul tuo
Cloudron](https://cloudron.io/store/org.wallabag.cloudronapp.html)
