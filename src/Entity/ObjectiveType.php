<?php

namespace App\Entity;

enum ObjectiveType: string
{
    case PROFESSIONNEL = 'professionnel';
    case SPORTIF = 'sportif';
    case SOCIAL = 'social';
    case SANTE = 'sante';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PROFESSIONNEL => 'Professionnel',
            self::SPORTIF => 'Sportif',
            self::SOCIAL => 'Social',
            self::SANTE => 'Santé',
        };
    }

    public function getSystemImageName(): string
    {
        return match($this) {
            self::PROFESSIONNEL => 'briefcase.fill',
            self::SPORTIF => 'figure.run',
            self::SOCIAL => 'person.2.fill',
            self::SANTE => 'heart.fill',
        };
    }
}

