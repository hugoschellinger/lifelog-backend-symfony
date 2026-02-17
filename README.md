# InitApi

![Logo](public/images//logo.png)

_InitApi_ est une api php utilisant le framework Symfony. Vous trouverez en dessous le manuel d'utilisation de cette api :

## Installation

Pour commencer, ce projet est fait pour être forker et utiliser comme base de travail. Vous devez donc créer un nouveau projet en forkant ce ce projet.

Voici les étapes pour installer ce projet :

### Installation des dépendances

Pour commencer, installer toutes les dépendances dont l' api a besoin :

* Dépendances js
> npm install

* dépendances php
> composer install

### Changement des variables d'environnement

Il faut commencer par copier le fichier `.env.local.dist` et le coller en le renommant `.env.local`.

A l'intérieur de ce fichier, vous devez renseigner les variable demandé :

JWT_PASSPHRASE = `PASSPHRASE_DES _LEFS_JWT` <sub>(commande : `php bin/console app:genere-jwt-passphrase`)</sub>

MAILER_DSN = `DSN_DU_MAILER`

Ensuite, modifier les variable du fichier `.env` :

APP_NAME = `NOM_DE_L'APPLICATION`

APP_SECRET = `CODE_SECRET_DE_L'APPLICATION` <sub>(commande : `php bin/console app:genere-app-secret`)</sub>
### Création des schémas

Il faut ensuite créer la base de donnée pour importer tous les entités que contient le projet :

> php bin/console d:d:c

Installer maintenant les entités :

> php bin/console d:s:u -f --complete

### Changement du logo

Vous pouvez ajouter le logo de votre application en remplaçant l'image `./public/images/logo.png` (Gardez le même nom et le même chemin)

### Création des clées JWT

Générer de nouvelle clé JWT avec la commande : 

> php bin/console lexik:jwt:generate-keypair --overwrite

***OU***

**ATTENTION :** Si la commande ne fonctionne pas, vous devez télécharger [openssl](https://www.openssl.org/) et suivre [ce poste](https://stackoverflow.com/questions/66252709/error-system-libraryfopenno-such-process)(Prenez la réponse de `Zache Leto` et remplacer toutes les occurences de `app/var` par `config`)

### Création des données mockées

Vous pouvez créer des données mockées avec la commande : 

>php bin/console d:f:l --append

Par défaut, cette commande va créer un compte admin :
* email : **admin@admin.fr**
* mot de passe : **admin**

## Lancement du serveur

Pour lancer le serveur, il suffit tous simplement d'utiliser l'outil de symfony en tapant :

> symfony serve `--no-tls`

**ATTENTION :** Il est très important de rajouter le `--no-tls` lorsque'on utiliser l'api avec une application mobile car le tls ne fonctionne pas pour développer sur mobile en mode développement.

## Valeurs ajoutées

### Création de mail

On peut créer des mails grâce au générateur de template `Twig`. Les mails déjà existants se trouvent dans le dossier `templates/emails`.

### Envoie de mail

L'api permet d'envoyer des mails à n'importe quelle utilisateur. Pour cela, il faut utiliser la function `App\Service\MailService::sendCustomEmail` ou `App\Service\MailService::send` pour les mails pré-enregistré.

Chaque mail est enregistré à travers l'entité `App\Entity\Mail`.

### Système de traduction

Un système de traduction est disponible sur l'api. Toutes les routes de l'api ont un préfix : `/api/{locale}`. Le mot `{locale}` peut prendre la valeur de n'importe quelle langage disponible dans la configuration du package `translation.yaml`. Lorsque l'on veut écrire du texte à renvoyer dans une réponse de requête, il faut utiliser le l'interface `Symfony\Contracts\Translation\TranslatorInterface` comme ceci :

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

Pour cela, il existe la commande suivante qui permet de voir les messages non traduits dans un langage spécifique :

> php bin/console translation:extract --dump-messages `fr`


On peut alors taper la commande suivante pour importer toute les traductions :

> php bin/console translation:extract --force fr

On peut ensuite modifier les traductions des différents langages dans le dossier `App\translation`(toutes les chaînes de caractère commençant par **__** sont à traduire).

On peut aussi debugger toutes les traductions grâce à la commande : 

> php bin/console debug:translation `fr`

**INFORMATIONS :** Pour plus d'information, la documentation est disponible [ici](https://symfony.com/doc/current/translation.html#the-translation-process)

### Enregistrement des erreurs

Chaque erreur est enregistré en base de donnée à l'aide du listener `App\EventListener\ExceptionListener`. Elles sont disponible dans l'entité `App\Entity\Exception`.

## Structure

Voici [un article](https://cours.davidannebicque.fr/symfony/semestre-3/architecture-de-symfony) pour comprendre la structure de Symfony

