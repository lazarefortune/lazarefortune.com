<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218230802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, position INT DEFAULT 0 NOT NULL, image VARCHAR(255) DEFAULT NULL, unlockable TINYINT(1) DEFAULT 0 NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, action VARCHAR(255) NOT NULL, action_count INT DEFAULT 0 NOT NULL, theme VARCHAR(255) DEFAULT \'grey\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge_unlock (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, badge_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_585813AA7E3C61F9 (owner_id), INDEX IDX_585813AAF7A2C2FC (badge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_session (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, quiz_id INT DEFAULT NULL, token VARCHAR(64) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', attempt_number INT DEFAULT 0 NOT NULL, time_limit INT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_C21E78745F37A13B (token), INDEX IDX_C21E7874A76ED395 (user_id), INDEX IDX_C21E7874853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge_unlock ADD CONSTRAINT FK_585813AA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE badge_unlock ADD CONSTRAINT FK_585813AAF7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_session ADD CONSTRAINT FK_C21E7874A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_session ADD CONSTRAINT FK_C21E7874853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE user ADD xp INT DEFAULT 0 NOT NULL, ADD quizzes_completed INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge_unlock DROP FOREIGN KEY FK_585813AA7E3C61F9');
        $this->addSql('ALTER TABLE badge_unlock DROP FOREIGN KEY FK_585813AAF7A2C2FC');
        $this->addSql('ALTER TABLE quiz_session DROP FOREIGN KEY FK_C21E7874A76ED395');
        $this->addSql('ALTER TABLE quiz_session DROP FOREIGN KEY FK_C21E7874853CD175');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE badge_unlock');
        $this->addSql('DROP TABLE quiz_session');
        $this->addSql('ALTER TABLE user DROP xp, DROP quizzes_completed');
    }
}
