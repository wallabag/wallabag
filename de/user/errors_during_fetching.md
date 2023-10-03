# Fehler während des Artikelladens

{% hint style="danger" %}
Diese übersetzte Dokumentation ist möglicherweise veraltet. Neuere Funktionen oder Anforderungen finden Sie in der [englischen Dokumentation](https://doc.wallabag.org/en/).
{% endhint %}

## Warum schlägt das Laden eines Artikels fehl?

Das kann verschiedene Ursachen haben:

-   Netzwerkprobleme
-   wallabag kann den Inhalt aufgrund der Websitestruktur nicht laden

## Wie kann ich helfen das zu beheben?

-   [indem du uns eine Mail mit der URL des Artikels
    sendest](mailto:hello@wallabag.org)
-   indem du versuchst das Laden des Artikels durch Erstellen einer
    Datei für den Artikel selbst zu beheben Du kannst [dieses
    Tool](http://siteconfig.fivefilters.org/) nutzen.

## Wie kann ich versuchen, einen Artikel erneut zu laden?

Wenn wallabag beim Laden eines Artikels fehlschlägt, kannst du auf den
erneut laden Button klicken (der dritte in dem unteren Bild).

![Inhalt neu laden](../../img/user/refetch.png)

## Ersatzweise Browser-Extension Wallabagger nutzen

Manchmal kann wallabag den Seiteninhalt nicht abrufen, weil die Site zum Seitenaufbau JavaScript erfordert oder Daten nachlädt oder generell versucht zu verhindern, dass Drittprogramme Daten abgreifen. Für Firefox, Chrome und Opera gibt es die Erweiterung "Wallabagger", hier kann man in den Einstellungen festlegen, dass der bereits interpretierte Seiteninhalt an wallabag geschickt wird und nicht nur der Link. In vielen Fällen kann man damit sehr gut arbeiten. Manchmal braucht man aber auch hier eine siteconfig, wie oben beschrieben.
