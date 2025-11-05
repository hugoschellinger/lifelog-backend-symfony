<?php

namespace App\Entity;

enum ObjectiveType: string
{
    case PROFESSIONNEL = 'professionnel';
    case SPORTIF = 'sportif';
    case SOCIAL = 'social';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PROFESSIONNEL => 'Professionnel',
            self::SPORTIF => 'Sportif',
            self::SOCIAL => 'Social',
        };
    }

    public function getSystemImageName(): string
    {
        return match($this) {
            self::PROFESSIONNEL => 'briefcase.fill',
            self::SPORTIF => 'figure.run',
            self::SOCIAL => 'person.2.fill',
        };
    }
}

