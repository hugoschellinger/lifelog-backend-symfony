<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106140911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les contraintes de clés étrangères
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT IF EXISTS FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT IF EXISTS FK_DADD4A25B2C71B3F');
        $this->addSql('ALTER TABLE global_objective DROP CONSTRAINT IF EXISTS FK_65979A9840C1FEA7');
        $this->addSql('ALTER TABLE goal DROP CONSTRAINT IF EXISTS FK_FCDCEB2E7084486A');
        $this->addSql('ALTER TABLE progression DROP CONSTRAINT IF EXISTS FK_D5B25073667D1AFE');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT IF EXISTS FK_B6F7494ECE07E8FF');
        $this->addSql('ALTER TABLE questionnaire DROP CONSTRAINT IF EXISTS FK_7A64DAF40C1FEA7');
        $this->addSql('ALTER TABLE response_session DROP CONSTRAINT IF EXISTS FK_7BFE93AACE07E8FF');

        // Convertir les colonnes ID et clés étrangères de UUID vers INT
        // Utilisation de USING avec abs(hashtext()) pour garantir des valeurs positives
        // Note: Cette conversion préserve approximativement les relations mais peut avoir des collisions
        // Si les tables contiennent des données importantes, considérez une migration de données plus complexe
        
        // Table year (doit être convertie en premier car référencée par d'autres tables)
        $this->addSql('ALTER TABLE year ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS year_id_seq');
        $this->addSql('SELECT setval(\'year_id_seq\', COALESCE((SELECT MAX(id) FROM year), 1), true)');
        $this->addSql('ALTER TABLE year ALTER id SET DEFAULT nextval(\'year_id_seq\')');
        $this->addSql('COMMENT ON COLUMN year.id IS NULL');
        
        // Table global_objective
        $this->addSql('ALTER TABLE global_objective ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS global_objective_id_seq');
        $this->addSql('SELECT setval(\'global_objective_id_seq\', COALESCE((SELECT MAX(id) FROM global_objective), 1), true)');
        $this->addSql('ALTER TABLE global_objective ALTER id SET DEFAULT nextval(\'global_objective_id_seq\')');
        $this->addSql('ALTER TABLE global_objective ALTER year_id TYPE INT USING abs(hashtext(year_id::text))::int');
        $this->addSql('COMMENT ON COLUMN global_objective.id IS NULL');
        $this->addSql('COMMENT ON COLUMN global_objective.year_id IS NULL');
        
        // Table questionnaire
        $this->addSql('ALTER TABLE questionnaire ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS questionnaire_id_seq');
        $this->addSql('SELECT setval(\'questionnaire_id_seq\', COALESCE((SELECT MAX(id) FROM questionnaire), 1), true)');
        $this->addSql('ALTER TABLE questionnaire ALTER id SET DEFAULT nextval(\'questionnaire_id_seq\')');
        $this->addSql('ALTER TABLE questionnaire ALTER year_id TYPE INT USING abs(hashtext(year_id::text))::int');
        $this->addSql('COMMENT ON COLUMN questionnaire.id IS NULL');
        $this->addSql('COMMENT ON COLUMN questionnaire.year_id IS NULL');
        
        // Table question
        $this->addSql('ALTER TABLE question ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS question_id_seq');
        $this->addSql('SELECT setval(\'question_id_seq\', COALESCE((SELECT MAX(id) FROM question), 1), true)');
        $this->addSql('ALTER TABLE question ALTER id SET DEFAULT nextval(\'question_id_seq\')');
        $this->addSql('ALTER TABLE question ALTER questionnaire_id TYPE INT USING abs(hashtext(questionnaire_id::text))::int');
        $this->addSql('COMMENT ON COLUMN question.id IS NULL');
        $this->addSql('COMMENT ON COLUMN question.questionnaire_id IS NULL');
        
        // Table goal
        $this->addSql('ALTER TABLE goal ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS goal_id_seq');
        $this->addSql('SELECT setval(\'goal_id_seq\', COALESCE((SELECT MAX(id) FROM goal), 1), true)');
        $this->addSql('ALTER TABLE goal ALTER id SET DEFAULT nextval(\'goal_id_seq\')');
        $this->addSql('ALTER TABLE goal ALTER global_objective_id TYPE INT USING abs(hashtext(global_objective_id::text))::int');
        $this->addSql('COMMENT ON COLUMN goal.id IS NULL');
        $this->addSql('COMMENT ON COLUMN goal.global_objective_id IS NULL');
        
        // Table progression
        $this->addSql('ALTER TABLE progression ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS progression_id_seq');
        $this->addSql('SELECT setval(\'progression_id_seq\', COALESCE((SELECT MAX(id) FROM progression), 1), true)');
        $this->addSql('ALTER TABLE progression ALTER id SET DEFAULT nextval(\'progression_id_seq\')');
        $this->addSql('ALTER TABLE progression ALTER goal_id TYPE INT USING abs(hashtext(goal_id::text))::int');
        $this->addSql('COMMENT ON COLUMN progression.id IS NULL');
        $this->addSql('COMMENT ON COLUMN progression.goal_id IS NULL');
        
        // Table response_session
        $this->addSql('ALTER TABLE response_session ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS response_session_id_seq');
        $this->addSql('SELECT setval(\'response_session_id_seq\', COALESCE((SELECT MAX(id) FROM response_session), 1), true)');
        $this->addSql('ALTER TABLE response_session ALTER id SET DEFAULT nextval(\'response_session_id_seq\')');
        $this->addSql('ALTER TABLE response_session ALTER questionnaire_id TYPE INT USING abs(hashtext(questionnaire_id::text))::int');
        $this->addSql('COMMENT ON COLUMN response_session.id IS NULL');
        $this->addSql('COMMENT ON COLUMN response_session.questionnaire_id IS NULL');
        
        // Table answer (doit être convertie en dernier car dépend des autres tables)
        $this->addSql('ALTER TABLE answer ALTER id TYPE INT USING abs(hashtext(id::text))::int');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS answer_id_seq');
        $this->addSql('SELECT setval(\'answer_id_seq\', COALESCE((SELECT MAX(id) FROM answer), 1), true)');
        $this->addSql('ALTER TABLE answer ALTER id SET DEFAULT nextval(\'answer_id_seq\')');
        $this->addSql('ALTER TABLE answer ALTER question_id TYPE INT USING abs(hashtext(question_id::text))::int');
        $this->addSql('ALTER TABLE answer ALTER response_session_id TYPE INT USING abs(hashtext(response_session_id::text))::int');
        $this->addSql('COMMENT ON COLUMN answer.id IS NULL');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS NULL');
        $this->addSql('COMMENT ON COLUMN answer.response_session_id IS NULL');
        
        // Recréer les contraintes de clés étrangères
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
        $this->addSql('ALTER TABLE goal ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE goal ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE goal ALTER global_objective_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN goal.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN goal.global_objective_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE global_objective ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE global_objective ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE global_objective ALTER year_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN global_objective.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN global_objective.year_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE questionnaire ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE questionnaire ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE questionnaire ALTER year_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN questionnaire.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN questionnaire.year_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE progression ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE progression ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE progression ALTER goal_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN progression.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN progression.goal_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE response_session ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE response_session ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE response_session ALTER questionnaire_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN response_session.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN response_session.questionnaire_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE question ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE question ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE question ALTER questionnaire_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN question.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN question.questionnaire_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE year ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE year ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN year.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE answer ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE answer ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE answer ALTER question_id TYPE UUID');
        $this->addSql('ALTER TABLE answer ALTER response_session_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.response_session_id IS \'(DC2Type:uuid)\'');
    }
}
