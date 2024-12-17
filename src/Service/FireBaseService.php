<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Notifier\Bridge\Firebase\Notification\WebNotification;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;

class FireBaseService{

    private ChatterInterface $chatter;
    private DeviceService $deviceService;

    public function __construct(ChatterInterface $chatter, DeviceService $deviceService)
    {
        $this->chatter=$chatter;
        $this->deviceService=$deviceService;
    }

    /**
     * Envoie une notification a tous les devices des utilisateurs séléctionné
     *
     * @param User $user Utilisateur qui reçoit la notification
     * @param string $title Titre de la notification
     * @param string $content Contenu de la notification
     * @return void
     */
    public function sendNotification(User $user,string $title, string $content){
        foreach($user->getDevice() as $device){
            $chatMessage=new ChatMessage($content,new WebNotification($device->getToken(),["title"=>$title], ["content"=>$content]));
            
            $message=$chatMessage->transport('firebase');
            try{
                $this->chatter->send($message);
            }catch(TransportExceptionInterface $e){
                $this->deviceService->delete($device);
                throw new TransportException("Erreur lors de l'envoie de la notification : ".$e->getMessage());
            }
        }
    }
}