<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_persistent and is_archived columns to question table, make year_id nullable for persistent questions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE question ADD COLUMN is_persistent BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE question ADD COLUMN is_archived BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE question ALTER COLUMN year_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE question SET year_id = (SELECT id FROM year LIMIT 1) WHERE year_id IS NULL');
        $this->addSql('ALTER TABLE question ALTER COLUMN year_id SET NOT NULL');
        $this->addSql('ALTER TABLE question DROP COLUMN is_archived');
        $this->addSql('ALTER TABLE question DROP COLUMN is_persistent');
    }
}
