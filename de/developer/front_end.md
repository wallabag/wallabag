# Tipps für Front-End Entwickler

{% hint style="danger" %}
Diese übersetzte Dokumentation ist möglicherweise veraltet. Neuere Funktionen oder Anforderungen finden Sie in der [englischen Dokumentation](https://doc.wallabag.org/en/).
{% endhint %}

Mit der Version 2.3 nutzt wallabag webpack, um seine Assets zu packen.

## Entwicklermodus

Wenn der Server im Entwicklermodus läuft, musst du `yarn build:dev`
ausführen, um die JavaScript-Dateien für jedes Designthema zu generieren.
Diese Dateien werden `%thema%.dev.js` genannt und werden von git
ignoriert. Du musst jedes Mal `yarn build:dev` erneut ausführen,
sobald auch nur eine der Asset-Dateien (js, css, Bilder, Schriftarten,
...).


## Live Neuladen

Webpack unterstützt das Live Neuladen, was bedeutet, dass du nicht die
Assets für jede Änderung neu genieren musst und auch nicht die Seite
manuell neu laden musst. Änderungen werden automatisch in der Seite
widergespiegelt. Setze nut die Option `use_webpack_dev_server` auf den
Wert `true` in der Datei `app/config/config.yml` und führe
`yarn watch` aus und es geht los.

Vergiss nicht `use_webpack_dev_server` wieder auf `false` zu setzen,
wenn du die Funktionalität Live Neuladen nicht mehr nutzen möchtest.


## Produktions-Builds

Wenn du deine Änderungen nutzen willst, baue sie in der
Produktionsumgebung durch das Ausführen von `yarn build:prod`.
Dies wird alle Assets für wallabag erstellen. Um zu testen, dass
alles ordentlich funktioniert, wirst du einen im Produktionsmodus
laufenden Server bruachen, z.B. mit
`bin/console server:run -e=prod`.

Vergiss nicht, die Produktions-Builds zu erstellen bevor du sie
einstellst.

# Code-Stil

Code-Stil wird durch zwei Tools überprüft: stylelint für (S)CSS und
eslint für JS. Die Eslint-Konfiguration basiert auf den Airbnb
Basisvoreinstellungen.
