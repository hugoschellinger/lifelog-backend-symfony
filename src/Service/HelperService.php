<?php

namespace App\Service;

class HelperService
{

    public function __construct()
    {
    }

    /**
     * Génère un mot de passe aléatoire
     */
    public function genereToken(int $length = 8,bool $onlyDigit = false, bool $withSpecialCharacter = false): string
    {
        $characters = '0123456789';
        if(!$onlyDigit){
            $characters .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            
            if($withSpecialCharacter){
                $characters .= '$&+,:;=?@#|\'<>.^*()%!-';
            }
        }
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}