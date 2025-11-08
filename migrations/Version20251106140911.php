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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE answer_id_seq');
        $this->addSql('SELECT setval(\'answer_id_seq\', (SELECT MAX(id) FROM answer))');
        $this->addSql('ALTER TABLE answer ALTER id SET DEFAULT nextval(\'answer_id_seq\')');
        $this->addSql('ALTER TABLE answer ALTER question_id TYPE INT');
        $this->addSql('ALTER TABLE answer ALTER response_session_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN answer.id IS NULL');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS NULL');
        $this->addSql('COMMENT ON COLUMN answer.response_session_id IS NULL');
        $this->addSql('ALTER TABLE global_objective ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE global_objective_id_seq');
        $this->addSql('SELECT setval(\'global_objective_id_seq\', (SELECT MAX(id) FROM global_objective))');
        $this->addSql('ALTER TABLE global_objective ALTER id SET DEFAULT nextval(\'global_objective_id_seq\')');
        $this->addSql('ALTER TABLE global_objective ALTER year_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN global_objective.id IS NULL');
        $this->addSql('COMMENT ON COLUMN global_objective.year_id IS NULL');
        $this->addSql('ALTER TABLE goal ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE goal_id_seq');
        $this->addSql('SELECT setval(\'goal_id_seq\', (SELECT MAX(id) FROM goal))');
        $this->addSql('ALTER TABLE goal ALTER id SET DEFAULT nextval(\'goal_id_seq\')');
        $this->addSql('ALTER TABLE goal ALTER global_objective_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN goal.id IS NULL');
        $this->addSql('COMMENT ON COLUMN goal.global_objective_id IS NULL');
        $this->addSql('ALTER TABLE progression ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE progression_id_seq');
        $this->addSql('SELECT setval(\'progression_id_seq\', (SELECT MAX(id) FROM progression))');
        $this->addSql('ALTER TABLE progression ALTER id SET DEFAULT nextval(\'progression_id_seq\')');
        $this->addSql('ALTER TABLE progression ALTER goal_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN progression.id IS NULL');
        $this->addSql('COMMENT ON COLUMN progression.goal_id IS NULL');
        $this->addSql('ALTER TABLE question ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE question_id_seq');
        $this->addSql('SELECT setval(\'question_id_seq\', (SELECT MAX(id) FROM question))');
        $this->addSql('ALTER TABLE question ALTER id SET DEFAULT nextval(\'question_id_seq\')');
        $this->addSql('ALTER TABLE question ALTER questionnaire_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN question.id IS NULL');
        $this->addSql('COMMENT ON COLUMN question.questionnaire_id IS NULL');
        $this->addSql('ALTER TABLE questionnaire ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE questionnaire_id_seq');
        $this->addSql('SELECT setval(\'questionnaire_id_seq\', (SELECT MAX(id) FROM questionnaire))');
        $this->addSql('ALTER TABLE questionnaire ALTER id SET DEFAULT nextval(\'questionnaire_id_seq\')');
        $this->addSql('ALTER TABLE questionnaire ALTER year_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN questionnaire.id IS NULL');
        $this->addSql('COMMENT ON COLUMN questionnaire.year_id IS NULL');
        $this->addSql('ALTER TABLE response_session ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE response_session_id_seq');
        $this->addSql('SELECT setval(\'response_session_id_seq\', (SELECT MAX(id) FROM response_session))');
        $this->addSql('ALTER TABLE response_session ALTER id SET DEFAULT nextval(\'response_session_id_seq\')');
        $this->addSql('ALTER TABLE response_session ALTER questionnaire_id TYPE INT');
        $this->addSql('COMMENT ON COLUMN response_session.id IS NULL');
        $this->addSql('COMMENT ON COLUMN response_session.questionnaire_id IS NULL');
        $this->addSql('ALTER TABLE year ALTER id TYPE INT');
        $this->addSql('CREATE SEQUENCE year_id_seq');
        $this->addSql('SELECT setval(\'year_id_seq\', (SELECT MAX(id) FROM year))');
        $this->addSql('ALTER TABLE year ALTER id SET DEFAULT nextval(\'year_id_seq\')');
        $this->addSql('COMMENT ON COLUMN year.id IS NULL');
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
