<?php

namespace App\Entity;

enum QuestionType: string
{
    case TEXT = 'text';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case SINGLE_CHOICE = 'single_choice';
    case SCALE = 'scale';
    case YES_NO = 'yes_no';
    case DATE = 'date';
    case NUMBER = 'number';

    public function getDisplayName(): string
    {
        return match($this) {
            self::TEXT => 'Texte libre',
            self::MULTIPLE_CHOICE => 'Choix multiples',
            self::SINGLE_CHOICE => 'Choix unique',
            self::SCALE => 'Échelle',
            self::YES_NO => 'Oui/Non',
            self::DATE => 'Date',
            self::NUMBER => 'Nombre',
        };
    }
}

