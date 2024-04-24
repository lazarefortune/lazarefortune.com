<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240424222837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachment (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, file_size INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, online TINYINT(1) DEFAULT 0 NOT NULL, premium TINYINT(1) DEFAULT 0 NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_FEC530A9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT NOT NULL, youtube_thumbnail_id INT DEFAULT NULL, deprecated_by_id INT DEFAULT NULL, formation_id INT DEFAULT NULL, duration SMALLINT DEFAULT 0 NOT NULL, youtube_id VARCHAR(255) DEFAULT NULL, video_path VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_169E6FB9C5BF4C20 (youtube_thumbnail_id), INDEX IDX_169E6FB9DCF7B613 (deprecated_by_id), INDEX IDX_169E6FB95200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation (id INT NOT NULL, deprecated_by_id INT DEFAULT NULL, short LONGTEXT DEFAULT NULL, youtube_playlist VARCHAR(255) DEFAULT NULL, INDEX IDX_404021BFDCF7B613 (deprecated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, type VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology_technology (technology_source INT NOT NULL, technology_target INT NOT NULL, INDEX IDX_29DA077915B5A9D8 (technology_source), INDEX IDX_29DA0779C50F957 (technology_target), PRIMARY KEY(technology_source, technology_target)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology_usage (technology_id INT NOT NULL, content_id INT NOT NULL, version VARCHAR(15) DEFAULT NULL, secondary TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_3098B4144235D463 (technology_id), INDEX IDX_3098B41484A0A3ED (content_id), PRIMARY KEY(technology_id, content_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9C5BF4C20 FOREIGN KEY (youtube_thumbnail_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9DCF7B613 FOREIGN KEY (deprecated_by_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB95200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9BF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFDCF7B613 FOREIGN KEY (deprecated_by_id) REFERENCES formation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFBF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_technology ADD CONSTRAINT FK_29DA077915B5A9D8 FOREIGN KEY (technology_source) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_technology ADD CONSTRAINT FK_29DA0779C50F957 FOREIGN KEY (technology_target) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_usage ADD CONSTRAINT FK_3098B4144235D463 FOREIGN KEY (technology_id) REFERENCES technology (id)');
        $this->addSql('ALTER TABLE technology_usage ADD CONSTRAINT FK_3098B41484A0A3ED FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9A76ED395');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9C5BF4C20');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9DCF7B613');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB95200282E');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9BF396750');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFDCF7B613');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFBF396750');
        $this->addSql('ALTER TABLE technology_technology DROP FOREIGN KEY FK_29DA077915B5A9D8');
        $this->addSql('ALTER TABLE technology_technology DROP FOREIGN KEY FK_29DA0779C50F957');
        $this->addSql('ALTER TABLE technology_usage DROP FOREIGN KEY FK_3098B4144235D463');
        $this->addSql('ALTER TABLE technology_usage DROP FOREIGN KEY FK_3098B41484A0A3ED');
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE technology');
        $this->addSql('DROP TABLE technology_technology');
        $this->addSql('DROP TABLE technology_usage');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }
}
