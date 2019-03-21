Benutzer-Informationen
----------------------

{% hint style="danger" %}
This translated documentation might be out of date. For more recent features or requirements, please refer to the [English documentation](https://doc.wallabag.org/en/).
{% endhint %}

Du kannst deinen Namen ändern, deine E-Mail-Adresse und die
Zwei-Faktor-Authentifizierung aktivieren.

### Zwei-Faktor-Authentifizierung (2FA)

> Die Zwei-Faktor-Authentifizierung (2FA) dient dem Identitätsnachweis
> eines Nutzers mittels der Kombination zweier verschiedener und
> insbesondere unabhängiger Komponenten (Faktoren).
>
> <https://de.wikipedia.org/wiki/Zwei-Faktor-Authentifizierung>

**Warnung:** Das Aktivieren von 2FA über das Konfigurations-Interface
ist nur möglich, wenn vorher in der app/config/parameters.yml die
twofactor\_auth-Eigenschaft auf true gesetzt wurde (nach der
Konfiguration das Leeren des Cache mit
php bin/console cache:clear -e=prod nicht vergessen).

Wenn du 2FA aktivierst, erhälst du jedes Mal, wenn du dich bei wallabag
einloggen willst, einen Code per Mail. Du musst den Code in das folgende
Formular eingeben.

![Zwei-Faktor-Authentifizierung](../../../img/user/2FA_form.png)

Wenn du nicht jedes Mal, wenn du dich einloggen willst, einen Code
zugesendet bekommen möchtest, kannst du die Checkbox
`Ich bin an einem persönlichen Computer` anhaken: wallabag wird sich an
dich für 15 Tage erinnern.
