# Informazioni utente

Potete cambiare il vostro nome, il vostro indirizzo e-mail e abilitare
l'`Autenticazione a due fattori`.

## Autenticazione a due fattori (2FA)

> L'autenticazione a due fattori (conosciuta anche come 2FA) é una
> tecnologia brevettata nel 1984 che offre l'identificazione degli
> utenti tramite una combinazione di due componenti differenti.
>
> <https://it.wikipedia.org/wiki/Autenticazione_a_due_fattori>

**Attenzione**: abilitare la 2FA dall'interfaccia di configurazione è
possibile solamente se ciò è stato abilitato precedentemente in
app/config/parameters.yml impostando la proprietà twofactor\_auth su
true (non dimenticate di eseguire il comando
php bin/console cache:clear -e=prod dopo la modifica).

Se abilitate la 2FA, ogni volta che vogliate accedere a wallabag,
riceverete un codice via email. Dovrete inserire il codice nel seguente
modulo.

![Autenticazine a due fattori](../../../img/user/2FA_form.png)

Se non volete ricevere il codice ogni volta che vogliate accedere,
potete spuntare la casella `Sono su un computer fidato`: wallabag vi
ricorderá per 15 giorni.
