<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105222525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id UUID NOT NULL, question_id UUID DEFAULT NULL, response_session_id UUID DEFAULT NULL, text_value VARCHAR(255) DEFAULT NULL, number_value DOUBLE PRECISION DEFAULT NULL, date_value TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, bool_value BOOLEAN DEFAULT NULL, selected_options JSON NOT NULL, answered_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
        $this->addSql('CREATE INDEX IDX_DADD4A25B2C71B3F ON answer (response_session_id)');
        $this->addSql('COMMENT ON COLUMN answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.response_session_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE global_objective (id UUID NOT NULL, year_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, objective_description TEXT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_65979A9840C1FEA7 ON global_objective (year_id)');
        $this->addSql('COMMENT ON COLUMN global_objective.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN global_objective.year_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE goal (id UUID NOT NULL, global_objective_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, goal_description TEXT DEFAULT NULL, measure DOUBLE PRECISION NOT NULL, measure_label VARCHAR(255) NOT NULL, target_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FCDCEB2E7084486A ON goal (global_objective_id)');
        $this->addSql('COMMENT ON COLUMN goal.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN goal.global_objective_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE progression (id UUID NOT NULL, goal_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, progression_description TEXT DEFAULT NULL, measure DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D5B25073667D1AFE ON progression (goal_id)');
        $this->addSql('COMMENT ON COLUMN progression.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN progression.goal_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE question (id UUID NOT NULL, questionnaire_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, question_description TEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, is_required BOOLEAN NOT NULL, "order" INT NOT NULL, options JSON NOT NULL, min_value DOUBLE PRECISION DEFAULT NULL, max_value DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494ECE07E8FF ON question (questionnaire_id)');
        $this->addSql('COMMENT ON COLUMN question.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN question.questionnaire_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE questionnaire (id UUID NOT NULL, year_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, questionnaire_description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A64DAF40C1FEA7 ON questionnaire (year_id)');
        $this->addSql('COMMENT ON COLUMN questionnaire.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN questionnaire.year_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE response_session (id UUID NOT NULL, questionnaire_id UUID DEFAULT NULL, session_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_completed BOOLEAN NOT NULL, completion_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, session_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7BFE93AACE07E8FF ON response_session (questionnaire_id)');
        $this->addSql('COMMENT ON COLUMN response_session.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN response_session.questionnaire_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE year (id UUID NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_value ON year (value)');
        $this->addSql('COMMENT ON COLUMN year.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25B2C71B3F FOREIGN KEY (response_session_id) REFERENCES response_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE global_objective ADD CONSTRAINT FK_65979A9840C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E7084486A FOREIGN KEY (global_objective_id) REFERENCES global_objective (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE progression ADD CONSTRAINT FK_D5B25073667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF40C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response_session ADD CONSTRAINT FK_7BFE93AACE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25B2C71B3F');
        $this->addSql('ALTER TABLE global_objective DROP CONSTRAINT FK_65979A9840C1FEA7');
        $this->addSql('ALTER TABLE goal DROP CONSTRAINT FK_FCDCEB2E7084486A');
        $this->addSql('ALTER TABLE progression DROP CONSTRAINT FK_D5B25073667D1AFE');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECE07E8FF');
        $this->addSql('ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAF40C1FEA7');
        $this->addSql('ALTER TABLE response_session DROP CONSTRAINT FK_7BFE93AACE07E8FF');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE global_objective');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE progression');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE response_session');
        $this->addSql('DROP TABLE year');
    }
}
