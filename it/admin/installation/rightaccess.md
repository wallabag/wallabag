# Diritti di accesso alle cartelle del progetto

## Ambiente di test

Quando vorremo solamente testare wallabag, eseguiremo il comando
`make run` per avviare la nostra istanza di wallabag e tutto funzionerá
correttamente poiché l’utente che ha iniziato il progetto puó accedere
alla cartella corrente senza problemi.

## Ambiente di produzione

Non appena useremo Apache o Nginx per accedere alla nostra istanza di
wallabag, e non avviandola con il comando `make run`, dovremo aver cura
di concedere i giusti diritti sulle giuste cartelle per far rimanere
sicure tutte le cartelle del progetto.

Per fare ció, il nome della cartella, conosciuta come `DocumentRoot`
(per Apache) o `root` (per Nginx), deve essere assolutamente accessibile
all’utente Apache/Nginx. Il suo nome è generalmente `www-data`, `apache`
o `nobody` (dipendendo dal sistema Linux utilizzato).

Quindi la cartella `/var/www/wallabag/web` deve essere accessibile da
quest’ultimo. Questo tuttavia potrebbe non essere sufficiente se solo ci
importa di questa cartella poiché potremmo incontrare una pagina bianca
o un errore 500 quando cerchiamo di accedere alla homepage del progetto.

Questo é dato dal fatto che dovremo concedere gli stessi diritti di
accesso di `/var/www/wallabag/web` alla cartella `/var/www/wallabag/var`
. Risolveremo quindi il problema con il seguente comando:

```bash
chown -R www-data:www-data /var/www/wallabag/var
```

Deve essere tutto uguale per le seguenti cartelle:

-   /var/www/wallabag/bin/
-   /var/www/wallabag/app/config/
-   /var/www/wallabag/vendor/
-   /var/www/wallabag/data/

inserendo

```bash
chown -R www-data:www-data /var/www/wallabag/bin
chown -R www-data:www-data /var/www/wallabag/app/config
chown -R www-data:www-data /var/www/wallabag/vendor
chown -R www-data:www-data /var/www/wallabag/data/
```

Altrimenti prima o poi incontreremo questi messaggi di errore:

```
Unable to write to the "bin" directory.
file_put_contents(app/config/parameters.yml): failed to open stream: Permission denied
file_put_contents(/.../wallabag/vendor/autoload.php): failed to open stream: Permission denied
```

### Regole aggiuntive per SELinux

se SELinux é abilitato sul vostro sistema, dovrete configurare contesti
aggiuntivi in modo che wallabag funzioni correttamente. Per controllare
se SELinux é abilitato, semplicemente inserisci ció che segue:

`getenforce`

Questo mostrerá `Enforcing` se SELinux é abilitato. Creare un nuovo
contesto coinvolge la seguente sintassi:

`semanage fcontext -a -t <context type> <full path>`

Per esempio:

`semanage fcontext -a -t httpd_sys_content_t "/var/www/wallabag(/.*)?"`

Questo applicherá ricorsivamente il constesto httpd\_sys\_content\_t
alla cartella wallabag e a tutti i file e cartelle sottostanti. Sono
necessarie le seguenti regole:

| Percorso completo  | Contesto |
| ------------- | ------------- |
| /var/www/wallabag(/.\*)?  | `httpd_sys_content_t`  |
| /var/www/wallabag/data(/.\*)?  | `httpd_sys_rw_content_t`  |
| /var/www/wallabag/var/logs(/.\*)?  | `httpd_log_t`  |
| /var/www/wallabag/var/cache(/.\*)?  | `httpd_cache_t`  |

Dopo aver creato questi contesti, inserite ció che segue per applicare
le vostre regole:

`restorecon -R -v /var/www/wallabag`

Potrete controllare i contesti in una cartella scrivendo `ls -lZ` e
potrete vedere tutte le regole correnti con `semanage fcontext -l -C`.

Se state installando il pacchetto latest-v2-package, é necessaria
un'ulteriore regola durante la configurazione iniziale:

`semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/wallabag/var"`

Dopo che siate acceduti con successo al vostro wallabag e abbiate
completato la configurazione iniziale, questo contesto puó essere
rimosso:

```bash
semanage fcontext -d -t httpd_sys_rw_content_t "/var/www/wallabag/var"
retorecon -R -v /var/www/wallabag/var
```
