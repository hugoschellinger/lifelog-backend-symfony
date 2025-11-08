<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251108125757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isActive to Question and change relation from Questionnaire to Year';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne is_active à la table question
        $this->addSql('ALTER TABLE question ADD COLUMN is_active BOOLEAN DEFAULT true NOT NULL');
        
        // Migrer les données : récupérer l'année via le questionnaire pour chaque question
        // Mettre à jour questionnaire_id avec year_id depuis le questionnaire
        $this->addSql('
            UPDATE question q
            SET questionnaire_id = (
                SELECT qn.year_id
                FROM questionnaire qn
                WHERE qn.id = q.questionnaire_id
            )
            WHERE q.questionnaire_id IS NOT NULL
        ');
        
        // Renommer la colonne questionnaire_id en year_id
        $this->addSql('ALTER TABLE question RENAME COLUMN questionnaire_id TO year_id');
        
        // Mettre à jour la contrainte de clé étrangère si nécessaire
        // (Doctrine gère généralement cela automatiquement, mais on peut le faire manuellement)
    }

    public function down(Schema $schema): void
    {
        // Renommer la colonne year_id en questionnaire_id
        $this->addSql('ALTER TABLE question RENAME COLUMN year_id TO questionnaire_id');
        
        // Migrer les données : récupérer le questionnaire via l'année
        // Note: Cette migration down peut être complexe si plusieurs questionnaires existent pour une année
        // Pour simplifier, on met NULL ou le premier questionnaire trouvé
        $this->addSql('
            UPDATE question q
            SET questionnaire_id = (
                SELECT qn.id
                FROM questionnaire qn
                WHERE qn.year_id = q.questionnaire_id
                LIMIT 1
            )
            WHERE q.questionnaire_id IS NOT NULL
        ');
        
        // Supprimer la colonne is_active
        $this->addSql('ALTER TABLE question DROP COLUMN is_active');
    }
}
