InitApiLogo

InitApi est une api php utilisant le framework Symfony. Vous trouverez en dessous le manuel d'utilisation de cette api :

Installation

Pour commencer, ce projet est fait pour être forker et utiliser comme base de travail. Vous devez donc créer un nouveau projet en forkant ce ce projet.

Voici les étapes pour installer ce projet :

Installation des dépendances

Pour commencer, installer toutes les dépendances dont l' api a besoin :





dépendances php



composer install

Changement des variables d'environnement

Il faut commencer par copier le fichier .env.local.dist et le coller en le renommant .env.local.

A l'intérieur de ce fichier, vous devez renseigner les variable demandé :

JWT_PASSPHRASE = PASSPHRASE (commande : `php bin/console app:genere-jwt-passphrase`)

MAILER_DSN = DSN_DU_MAILER

Ensuite, modifier les variable du fichier .env :

APP_NAME = NOM_DE_L'APPLICATION

APP_SECRET = CODE_SECRET_DE_L'APPLICATION (commande : `php bin/console app:genere-app-secret`)

Création des schémas

Il faut ensuite créer la base de donnée pour importer tous les entités que contient le projet :



php bin/console d:d:c

Installer maintenant les entités :



php bin/console d:s:u -f --complete

Changement du logo

Vous pouvez ajouter le logo de votre application en remplaçant l'image ./public/images/logo.png (Gardez le même nom et le même chemin)

Création des clées JWT

Générer de nouvelle clé JWT avec la commande : 



php bin/console lexik:jwt:generate-keypair --overwrite

OU

ATTENTION : Si la commande ne fonctionne pas, vous devez télécharger openssl et suivre ce poste(Prenez la réponse de Zache Leto et remplacer toutes les occurences de app/var par config)

Création d’un utilisateur

Pour créer un utilisateur avec l’email et le mot de passe de votre choix :



php bin/console app:user:create

La commande vous demandera email et mot de passe si vous ne les fournissez pas en options. Vous pouvez aussi les passer directement :



php bin/console app:user:create --email=admin@example.com --password=monMotDePasse

Options disponibles :







Option



Description





--email



Email de l’utilisateur





--password



Mot de passe en clair





--firstname



Prénom (défaut : User)





--lastname



Nom





--admin



Attribue le rôle ROLE_ADMIN

Exemple avec rôle admin :



php bin/console app:user:create --email=admin@example.com --password=secret --admin

Données mockées (autres entités)

Si le projet contient d’autres fixtures (entités autres qu’utilisateur), vous pouvez les charger avec :



php bin/console d:f:l --append

Lancement du serveur

Pour lancer le serveur, il suffit tous simplement d'utiliser l'outil de symfony en tapant :



symfony serve --no-tls

ATTENTION : Il est très important de rajouter le --no-tls lorsque'on utiliser l'api avec une application mobile car le tls ne fonctionne pas pour développer sur mobile en mode développement.

Valeurs ajoutées

Système Messenger (Files d'attente asynchrones)

L'API utilise Symfony Messenger pour traiter les tâches de manière asynchrone, notamment l'envoi d'emails. Cela permet de :





Améliorer les performances : Les requêtes HTTP ne sont plus bloquées par l'envoi d'emails



Fiabilité : Les messages sont stockés dans Redis et peuvent être retraités en cas d'erreur



Scalabilité : Possibilité de lancer plusieurs workers pour traiter plus de messages en parallèle

Transport utilisé : Redis (configuré via MESSENGER_TRANSPORT_DSN)

Voir la section "Envoie de mail" ci-dessous pour plus de détails sur la configuration Supervisor.

Création de mail

On peut créer des mails grâce au générateur de template Twig. Les mails déjà existants se trouvent dans le dossier templates/emails.

Envoie de mail

L'api permet d'envoyer des mails à n'importe quelle utilisateur. Pour cela, il faut utiliser la fonction App\Service\MailService::send pour les mails pré-enregistrés.

Les mails sont envoyés de manière asynchrone via Symfony Messenger, ce qui permet de ne pas bloquer la requête HTTP pendant l'envoi. Le système utilise un transport Redis pour la file d'attente des messages.

Architecture Messenger





Message : App\Message\Mail - Contient les données nécessaires à l'envoi (destinataire, sujet, template, contexte)



Handler : App\MessageHandler\MailHandler - Traite le message et envoie effectivement l'email via MailerInterface



Service : App\Service\MailService - Dispatch les messages dans la file d'attente via MessageBusInterface

Configuration

Le transport async est configuré dans config/packages/messenger.yaml et utilise Redis (configuré via MESSENGER_TRANSPORT_DSN dans .env).

Consommation des messages en production

Pour que les messages soient traités automatiquement en production, il faut installer Supervisor qui va lancer et maintenir le worker Messenger en vie.

Installation de Supervisor (Ubuntu/Debian) :

sudo apt-get update
sudo apt-get install supervisor

Configuration Supervisor :

Créez le fichier /etc/supervisor/conf.d/messenger-worker.conf :

[program:messenger-worker]
command=php /chemin/vers/votre/projet/bin/console messenger:consume async --time-limit=3600
user=www-data
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d
stdout_logfile=/var/log/messenger-worker.log
stderr_logfile=/var/log/messenger-worker-error.log

Important :





Remplacez /chemin/vers/votre/projet par le chemin absolu vers votre projet



numprocs=2 lance 2 workers en parallèle (ajustez selon vos besoins)



--time-limit=3600 redémarre le worker toutes les heures pour éviter les fuites mémoire

Démarrer Supervisor :

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start messenger-worker:*

Commandes utiles :

# Voir le statut des workers
sudo supervisorctl status messenger-worker:*

# Redémarrer tous les workers
sudo supervisorctl restart messenger-worker:*

# Voir les logs
tail -f /var/log/messenger-worker.log

En développement :

Pour tester localement, vous pouvez consommer les messages manuellement :

php bin/console messenger:consume async

Système de traduction

Un système de traduction est disponible sur l'api. Toutes les routes de l'api ont un préfix : /api/{locale}. Le mot {locale} peut prendre la valeur de n'importe quelle langage disponible dans la configuration du package translation.yaml. Lorsque l'on veut écrire du texte à renvoyer dans une réponse de requête, il faut utiliser le l'interface Symfony\Contracts\Translation\TranslatorInterface comme ceci :

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

Il faut maintenant traduire tous les chaînes de caractère que l'on donne au TranslatorInterface.

Pour cela, il existe la commande suivante qui permet de voir les messages non traduits dans un langage spécifique :



php bin/console translation:extract --dump-messages fr

On peut alors taper la commande suivante pour importer toute les traductions :



php bin/console translation:extract --force fr

On peut ensuite modifier les traductions des différents langages dans le dossier App\translation(toutes les chaînes de caractère commençant par __ sont à traduire).

On peut aussi debugger toutes les traductions grâce à la commande : 



php bin/console debug:translation fr

INFORMATIONS : Pour plus d'information, la documentation est disponible ici

Enregistrement des erreurs

Chaque erreur est enregistré en base de donnée à l'aide du listener App\EventListener\ExceptionListener. Elles sont disponible dans l'entité App\Entity\Exception.

Structure

Voici un article pour comprendre la structure de Symfony