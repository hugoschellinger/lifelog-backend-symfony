# InitApi

![Logo](public/image//logo.png)

_InitApi_ est une api en php utilisant le framework symfony qui fonctione avec le projet [initProject](https://github.com/hugoschellinger/InitProject). Vous trouverz en dessous la notice d'utilisation de cette api :

## Installation

Pour commencer, ce projet est fait pour être forker et utiliser comme base de travail. Vous devez donc créer un nouveau projet en forkant ce ce projet. Ce projet fonctionne avec le projet de développement mobile [InitProject](https://github.com/hugoschellinger/InitProject) qu' il est conseillé d'utiliser.

Voici les étapes pour installer ce projet :

### Changement des variables d'environnement

Il faut commencer par changer les variables d'environnement situé dans le ficher ```.env``` : 

MAIL_DSN : `"smtp://EMAIL:PASSWORD@SMTP_MAIL:SMTP_PORT"` [<sub>(Doc ici)</sub>](https://symfony.com/doc/current/mailer.html)

DATABASE_URL : `"mysql://root:@127.0.0.1:3306/DATABASE_NAME?serverVersion=5.7&charset=utf8mb4"`

JWT_PASSPHRASE : `"PASSPHRASE"`

DOMAIN : `"http://DOMAIN:PORT"`


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

### Création des data fixture

Vous pouvez créer des données mockées avec la commande : 

>php bin/console d:f:l --append

Par défaut, cette commande va créer un compte admin :
* email : **admin**
* mot de passe : **admin**

### Configuration firebase

Pour configurer firebase, il faut créer une application firebase sur [leur site](https://console.firebase.google.com/u/0/?_gl=1*7s66mq*_ga*OTI3NjI1NjYyLjE2Nzg5NTMwNTQ.*_ga_CW55HF8NVT*MTY4NzA4MzM1OC4xMC4wLjE2ODcwODMzNTguMC4wLjA.)

Copier coller le fichier `.env.local.dist` en `.env.local` et récupérer le token firebase de l'application pour le coller dans ce fichier.

## Lancement du serveur

Pour lancer le serveur, il suffit tous simplement d'utiliser l'outil de symfony en tapant :

> symfony serve --no-tls

**ATTENTION :** Il est très important de rajouter le `--no-tls` lorsque'on utiliser l'api avec une application mobile car le tls ne fonctionne pas pour développer sur sur mobile en mode développement.

## Valeurs ajoutées

### Noticication firebase

L'api permet d'envoyer des notification push a tous les appareil de toute les utilisateurs de l'application. Pour cela, il faut utiliser la fonction `App\Service\FireBaseService::sendNotification`.

### Création de mail

On peut créer des mail grâce au générateur de template `Twig`. Les mails déjà existant se trouvent dans le dossier `templates/emails`.

### Envoie de mail

L'api permet d'envoyer des mails à n'importe quelle utilisateur. Pour cela, il faut utiliser la function `App\Service\MailService::sendCustomEmail` ou `App\Service\MailService::send` pour les mails pré-enregistrer.

Chaque mail est enregistrer à travers l'entité `App\Entity\Mail`.

### Système de traduction

Un système de traduction est disponible sur l'api. Toutes les routes de l'api ont un préfix : `/api/{locale}`. Le mot `{locale}` peut prendre la valeur de n'impporte quelle langage disponible dans la configuration du package `translation.yaml`. Lorsque l'on veut écrire du text à renvoyer dans une réponse de requête, il faut utiliser le l'interface `Symfony\Contracts\Translation\TranslatorInterface` comme ceci :

```php
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\Cache\Exception\FeatureNotImplemented;

class SecurityController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(UserService $userService, TranslatorInterface $translator, SecurityService $securityService)
    {
        $this->translator = $translator;
    }

    #[Route('/test', name: 'test', methods: ["GET"])]
    public function test(): Response
    {
        throw new FeatureNotImplemented($this->translator->trans("not implemented"));
    }
```

Il faut maintenant traduire tous les chaînes de caractère que l'on donne au `TranslatorInterface`.

Pour cela, il existe la commande suivante qui permet de voir les message non traduit dans un langage spécifique :

> php bin/console translation:extract --dump-messages `fr`


On peut alors taper la commande suivante pour importer toute les traductions :

> php bin/console translation:extract --force fr

On peut ensuite modifier les traductions des différents langages dans le dossier `App\translation`(toutes les chaînes de caractère commençant par **__** sont à traduire).

On peut aussi debuger toutes les traduction grace à la commande : 

> php bin/console debug:translation `fr`

**INFORMATIONS :** La documentation est disponible [ici](https://symfony.com/doc/current/translation.html#the-translation-process) si jamais

### Enregistrement des erreurs

Chaque erreurs est enregistrer en base de donnée à l'aide du listener `App\EventListener\ExceptionListener`. Elle sont disponible dans l'entité `App\Entity\Exception`.

## Structure

Voici [un article](https://cours.davidannebicque.fr/symfony/semestre-3/architecture-de-symfony) pour comprendre la structure de Symfony

