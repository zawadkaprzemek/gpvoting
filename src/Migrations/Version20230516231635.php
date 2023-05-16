<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516231635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exception_log CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE general_meeting CHANGE participant_list_id participant_list_id INT DEFAULT NULL, CHANGE kworum_required_value kworum_required_value INT DEFAULT NULL, CHANGE kworum_type kworum_type VARCHAR(255) DEFAULT NULL, CHANGE active_voting active_voting INT DEFAULT NULL, CHANGE last_voting last_voting INT DEFAULT NULL, CHANGE kworum_value kworum_value DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_answer CHANGE meeting_voting_id meeting_voting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_voting CHANGE tochoose tochoose INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD verified TINYINT(1) NOT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE surname hash VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE polling CHANGE code_id code_id INT DEFAULT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reset_password_request CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resolution CHANGE votes_on_count votes_on_count LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE votes_against_count votes_against_count LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE votes_hold_count votes_hold_count LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE accepted accepted TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE session_settings CHANGE answer_start answer_start DATETIME DEFAULT NULL, CHANGE answer_end answer_end DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE pack_id pack_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE last_ip last_ip VARCHAR(255) DEFAULT NULL, CHANGE last_visit last_visit DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exception_log CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE general_meeting CHANGE participant_list_id participant_list_id INT DEFAULT NULL, CHANGE kworum_required_value kworum_required_value INT DEFAULT NULL, CHANGE kworum_type kworum_type VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE active_voting active_voting INT DEFAULT NULL, CHANGE last_voting last_voting INT DEFAULT NULL, CHANGE kworum_value kworum_value DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE meeting_answer CHANGE meeting_voting_id meeting_voting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting_voting CHANGE tochoose tochoose INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE participant DROP verified, CHANGE phone phone VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE hash surname VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE polling CHANGE code_id code_id INT DEFAULT NULL, CHANGE end_date end_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reset_password_request CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resolution CHANGE votes_on_count votes_on_count LONGTEXT CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', CHANGE votes_against_count votes_against_count LONGTEXT CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', CHANGE votes_hold_count votes_hold_count LONGTEXT CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', CHANGE accepted accepted TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE session_settings CHANGE answer_start answer_start DATETIME DEFAULT \'NULL\', CHANGE answer_end answer_end DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE parent_id parent_id INT DEFAULT NULL, CHANGE pack_id pack_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE last_ip last_ip VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE last_visit last_visit DATETIME DEFAULT \'NULL\'');
    }
}
