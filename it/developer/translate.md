# Tradurre wallabag

## wallabag web app

### File per la traduzione

Visto che wallabag è principalmente sviluppato da un team francese, si
prega di considerare che la traduzione francese è la più aggiornata, e
si prega di copiarla e di creare la vostra traduzione.

Potete trovare qui i file per la traduzione:
<https://github.com/wallabag/wallabag/tree/master/src/Wallabag/CoreBundle/Resources/translations>.

Dovrete creare `messages.CODE.yml` e `validators.CODE.yml`, dove CODE è
il codice ISO 639-1 della vostra lingua ([guardate
wikipedia](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)).

Altri file da tradurre:

-   <https://github.com/wallabag/wallabag/tree/master/app/Resources/CraueConfigBundle/translations>.
-   <https://github.com/wallabag/wallabag/tree/master/src/Wallabag/UserBundle/Resources/translations>.

Dovete creare i file `THE_TRANSLATION_FILE.CODE.yml`.

### File di configurazione

Dovete modificare
[app/config/wallabag.yml](https://github.com/wallabag/wallabag/blob/master/app/config/wallabag.yml)
per mostrare la vostra lingua nella pagina di configurazione di
wallabag (per consentire agli utenti di passare a questa nuova
traduzione).

Nella sezione `wallabag_core.languages`, dovete aggiungere una nuova
linea con la vostra traduzione. Per esempio:

```yaml
wallabag_core:
    ...
    languages:
        en: 'English'
        fr: 'Français'
```

Nella prima colonna (`en`, `fr`, etc.), dovete aggiungere il codice ISO
639-1 della vostra lingua (vedete sopra).

Nella seconda colonna, aggiungete solamente il nome della vostra lingua.

## Documentazione di wallabag

Contrariamente alla web app, il linguaggio principale per la
documentazione è l'inglese.

I file della documentazione sono memorizzati qui:
<https://github.com/wallabag/doc>

Dovete rispettare la struttura della cartella `en` quando create la
vostra traduzione.
