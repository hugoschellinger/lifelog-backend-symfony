<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251108130508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename order column to display_order and fix foreign key constraints';
    }

    public function up(Schema $schema): void
    {
        // Renommer la colonne "order" en "display_order" pour éviter les conflits avec le mot réservé SQL
        $this->addSql('ALTER TABLE question RENAME COLUMN "order" TO display_order');
        
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP CONSTRAINT fk_b6f7494ece07e8ff');
        $this->addSql('ALTER TABLE question ALTER is_active DROP DEFAULT');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E40C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_b6f7494ece07e8ff RENAME TO IDX_B6F7494E40C1FEA7');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494E40C1FEA7');
        $this->addSql('ALTER TABLE question ALTER is_active SET DEFAULT true');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT fk_b6f7494ece07e8ff FOREIGN KEY (year_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_b6f7494e40c1fea7 RENAME TO idx_b6f7494ece07e8ff');
        
        // Renommer la colonne display_order en "order"
        $this->addSql('ALTER TABLE question RENAME COLUMN display_order TO "order"');
    }
}
