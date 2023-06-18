# InitApi

![Logo](public/image//logo.png)

_InitApi_ est une api en php utilisant le framework symfony qui fonctione avec le projet [initProject](https://github.com/hugoschellinger/InitProject). Vous trouverz en dessous la notice d'utilisation de cette api :

## Installation

Pour commencer, ce projet est fait pour être forker et utiliser comme base de travail. Vous devez donc créer un nouveau projet en forkant ce ce projet. Ce projet fonctionne avec le projet de développement mobile [InitProject](https://github.com/hugoschellinger/InitProject) qu' il est conseillé d'utiliser.

Voici les étapes pour installer ce projet :

### Changement des variables d'environnement

Il faut commencer par changer les variables d'environnement situé dans le ficher ```.env``` : 

MAIL_DSN : ```"smtp://EMAIL:PASSWORD@SMTP_MAIL:SMTP_PORT"``` [<sub>(Doc ici)</sub>](https://symfony.com/doc/current/mailer.html)

DATABASE_URL : ```"mysql://root:@127.0.0.1:3306/DATABASE_NAME?serverVersion=5.7&charset=utf8mb4"```

JWT_PASSPHRASE : ```"PASSPHRASE"```

DOMAIN : ```"http://DOMAIN:PORT"```

### Installation des dépendances

Pour commencer, installer toute les dépendances dont l' api a besoin :

* Dépendances js
> npm install

* déendances php
> composer install

### Création des schémas

Il faut ensuite créer la base de donnée pour importer tous les entités que contient le projet :

> php bin/console d:d:c

Installer maintenant les entités :

> php bin/console d:s:u -f --complete

### Changement du logo

Vous pouvez ajouter le logo de votre application en remplaçant l'image `./public/images/logo.png` (Gardez le même non que l'ancienne image)

### Création des clées JWT

Générer de nouvelle clé JWT avec la commande : 

> php bin/console lexik:jwt:generate-keypair --overwrite

**ATTENTION :** Si la commande ne fonctionne pas, vous devez télécharger [openssl](https://www.openssl.org/) et suivre [ce poste](https://stackoverflow.com/questions/66252709/error-system-libraryfopenno-such-process)

