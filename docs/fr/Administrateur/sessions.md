---
language: Français
currentMenu: sessions
subTitle: Problème de sessions
---

Si vous vous retrouvez à être déconnecté même après avoir valider le *Stay signed in checkbox*,
lancez les commandes suivantes comme administrateur (ou avec sudo) :

```
mkdir /var/lib/wallabag-sessions
chown www-data:www-data /var/lib/wallabag-sessions
```

*NOTE : L'utilisateur et le groupe www-data pourrait ne pas exister.
Vous pouvez alors utiliser ```chown http:http /var/lib/wallabag-sessions``` à la place.*

Ensuite, en utilisant apache, ajoutez : `php_admin_value session.save_path /var/lib/wallabag-sessions` 
à votre vhost apache, tel que `wallabag-apache.conf`.
Enfin, redémarrez apache, en lançant par exemple : ```/etc/init.d/apache2 restart``` 

Si vous utilisez nginx, ajoutez  `php_admin_value[session.save_path] = /var/lib/wallabag-sessions`
à votre fichier de configuration de nginx.
Ensuite, redémarrez nginx : ```/etc/init.d/nginx restart```

*NOTE : si vous utilisez systemd, vous devriez faire  `systemctl restart apache2` (ou nginx).*
