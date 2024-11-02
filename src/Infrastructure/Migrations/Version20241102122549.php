<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102122549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachment (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, file_size INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, attachment_id INT DEFAULT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, online TINYINT(1) DEFAULT 0 NOT NULL, premium TINYINT(1) DEFAULT 0 NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_FEC530A9464E68B (attachment_id), INDEX IDX_FEC530A9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT NOT NULL, youtube_thumbnail_id INT DEFAULT NULL, deprecated_by_id INT DEFAULT NULL, formation_id INT DEFAULT NULL, duration SMALLINT DEFAULT 0 NOT NULL, youtube_id VARCHAR(255) DEFAULT NULL, video_path VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_169E6FB9C5BF4C20 (youtube_thumbnail_id), INDEX IDX_169E6FB9DCF7B613 (deprecated_by_id), INDEX IDX_169E6FB95200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_verification (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FE22358F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation (id INT NOT NULL, deprecated_by_id INT DEFAULT NULL, short LONGTEXT DEFAULT NULL, youtube_playlist VARCHAR(255) DEFAULT NULL, chapters JSON NOT NULL, INDEX IDX_404021BFDCF7B613 (deprecated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `option` (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_reset (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B1017252F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE progress (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, content_id INT NOT NULL, progress INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_2201F246F675F31B (author_id), INDEX IDX_2201F24684A0A3ED (content_id), UNIQUE INDEX progress_unique (author_id, content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, type VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology_requirement (technology_source INT NOT NULL, technology_target INT NOT NULL, INDEX IDX_FA5618B015B5A9D8 (technology_source), INDEX IDX_FA5618B0C50F957 (technology_target), PRIMARY KEY(technology_source, technology_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology_usage (technology_id INT NOT NULL, content_id INT NOT NULL, version VARCHAR(15) DEFAULT NULL, secondary TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_3098B4144235D463 (technology_id), INDEX IDX_3098B41484A0A3ED (content_id), PRIMARY KEY(technology_id, content_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, fullname VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, phone VARCHAR(255) DEFAULT NULL, date_of_birthday DATE DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, deleted_at DATE DEFAULT NULL, cgu TINYINT(1) NOT NULL, is_request_delete TINYINT(1) DEFAULT NULL, stripe_id LONGTEXT DEFAULT NULL, api_key VARCHAR(255) DEFAULT NULL, access_token LONGTEXT DEFAULT NULL, refresh_token LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9464E68B FOREIGN KEY (attachment_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9C5BF4C20 FOREIGN KEY (youtube_thumbnail_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9DCF7B613 FOREIGN KEY (deprecated_by_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB95200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9BF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email_verification ADD CONSTRAINT FK_FE22358F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFDCF7B613 FOREIGN KEY (deprecated_by_id) REFERENCES formation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFBF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE password_reset ADD CONSTRAINT FK_B1017252F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE progress ADD CONSTRAINT FK_2201F246F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE progress ADD CONSTRAINT FK_2201F24684A0A3ED FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_requirement ADD CONSTRAINT FK_FA5618B015B5A9D8 FOREIGN KEY (technology_source) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_requirement ADD CONSTRAINT FK_FA5618B0C50F957 FOREIGN KEY (technology_target) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_usage ADD CONSTRAINT FK_3098B4144235D463 FOREIGN KEY (technology_id) REFERENCES technology (id)');
        $this->addSql('ALTER TABLE technology_usage ADD CONSTRAINT FK_3098B41484A0A3ED FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9464E68B');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9A76ED395');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9C5BF4C20');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9DCF7B613');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB95200282E');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9BF396750');
        $this->addSql('ALTER TABLE email_verification DROP FOREIGN KEY FK_FE22358F675F31B');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFDCF7B613');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFBF396750');
        $this->addSql('ALTER TABLE password_reset DROP FOREIGN KEY FK_B1017252F675F31B');
        $this->addSql('ALTER TABLE progress DROP FOREIGN KEY FK_2201F246F675F31B');
        $this->addSql('ALTER TABLE progress DROP FOREIGN KEY FK_2201F24684A0A3ED');
        $this->addSql('ALTER TABLE technology_requirement DROP FOREIGN KEY FK_FA5618B015B5A9D8');
        $this->addSql('ALTER TABLE technology_requirement DROP FOREIGN KEY FK_FA5618B0C50F957');
        $this->addSql('ALTER TABLE technology_usage DROP FOREIGN KEY FK_3098B4144235D463');
        $this->addSql('ALTER TABLE technology_usage DROP FOREIGN KEY FK_3098B41484A0A3ED');
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE `option`');
        $this->addSql('DROP TABLE password_reset');
        $this->addSql('DROP TABLE progress');
        $this->addSql('DROP TABLE technology');
        $this->addSql('DROP TABLE technology_requirement');
        $this->addSql('DROP TABLE technology_usage');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
