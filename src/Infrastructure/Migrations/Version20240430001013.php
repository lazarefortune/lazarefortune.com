<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240430001013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, transaction_id INT DEFAULT NULL, prestation_id INT NOT NULL, comment LONGTEXT DEFAULT NULL, nb_adults INT NOT NULL, nb_children INT NOT NULL, date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, status VARCHAR(50) NOT NULL, sub_total INT DEFAULT NULL, total INT DEFAULT NULL, amount_paid INT DEFAULT NULL, applied_discount INT DEFAULT NULL, access_token VARCHAR(50) DEFAULT NULL, is_paid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_FE38F84419EB6921 (client_id), INDEX IDX_FE38F8442FC0CB0F (transaction_id), INDEX IDX_FE38F8449E45C554 (prestation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attachment (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, file_size INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, attachment_id INT DEFAULT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, online TINYINT(1) DEFAULT 0 NOT NULL, premium TINYINT(1) DEFAULT 0 NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_FEC530A9464E68B (attachment_id), INDEX IDX_FEC530A9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT NOT NULL, youtube_thumbnail_id INT DEFAULT NULL, deprecated_by_id INT DEFAULT NULL, formation_id INT DEFAULT NULL, duration SMALLINT DEFAULT 0 NOT NULL, youtube_id VARCHAR(255) DEFAULT NULL, video_path VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_169E6FB9C5BF4C20 (youtube_thumbnail_id), INDEX IDX_169E6FB9DCF7B613 (deprecated_by_id), INDEX IDX_169E6FB95200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_verification (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FE22358F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation (id INT NOT NULL, deprecated_by_id INT DEFAULT NULL, short LONGTEXT DEFAULT NULL, youtube_playlist VARCHAR(255) DEFAULT NULL, chapters JSON NOT NULL, INDEX IDX_404021BFDCF7B613 (deprecated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE holiday (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, UNIQUE INDEX UNIQ_DC9AB2342B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_realisation (id INT AUTO_INCREMENT NOT NULL, realisation_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_25732A80B685E551 (realisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `option` (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_reset (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B1017252F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, transaction_id INT NOT NULL, amount INT NOT NULL, status VARCHAR(50) NOT NULL, session_id VARCHAR(255) DEFAULT NULL, payment_method VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_6D28840D2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_available_to_client TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, category_prestation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, price INT DEFAULT NULL, duration TIME DEFAULT NULL, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, avalaible_space_per_prestation INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, consider_children_for_price TINYINT(1) DEFAULT NULL, children_age_range INT DEFAULT NULL, children_price_percentage DOUBLE PRECISION DEFAULT NULL, INDEX IDX_51C88FAD809EE01F (category_prestation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestation_tag (prestation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_4C50F389E45C554 (prestation_id), INDEX IDX_4C50F38BAD26311 (tag_id), PRIMARY KEY(prestation_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE realisation (id INT AUTO_INCREMENT NOT NULL, online TINYINT(1) NOT NULL, date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, type VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology_requirement (technology_source INT NOT NULL, technology_target INT NOT NULL, INDEX IDX_FA5618B015B5A9D8 (technology_source), INDEX IDX_FA5618B0C50F957 (technology_target), PRIMARY KEY(technology_source, technology_target)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technology_usage (technology_id INT NOT NULL, content_id INT NOT NULL, version VARCHAR(15) DEFAULT NULL, secondary TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_3098B4144235D463 (technology_id), INDEX IDX_3098B41484A0A3ED (content_id), PRIMARY KEY(technology_id, content_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, amount INT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_723705D119EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, fullname VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, phone VARCHAR(255) DEFAULT NULL, date_of_birthday DATE DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATE DEFAULT NULL, cgu TINYINT(1) NOT NULL, is_request_delete TINYINT(1) DEFAULT NULL, stripe_id LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84419EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8442FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8449E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9464E68B FOREIGN KEY (attachment_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9C5BF4C20 FOREIGN KEY (youtube_thumbnail_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9DCF7B613 FOREIGN KEY (deprecated_by_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB95200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9BF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email_verification ADD CONSTRAINT FK_FE22358F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFDCF7B613 FOREIGN KEY (deprecated_by_id) REFERENCES formation (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFBF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_realisation ADD CONSTRAINT FK_25732A80B685E551 FOREIGN KEY (realisation_id) REFERENCES realisation (id)');
        $this->addSql('ALTER TABLE password_reset ADD CONSTRAINT FK_B1017252F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD809EE01F FOREIGN KEY (category_prestation_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE prestation_tag ADD CONSTRAINT FK_4C50F389E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prestation_tag ADD CONSTRAINT FK_4C50F38BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_requirement ADD CONSTRAINT FK_FA5618B015B5A9D8 FOREIGN KEY (technology_source) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_requirement ADD CONSTRAINT FK_FA5618B0C50F957 FOREIGN KEY (technology_target) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technology_usage ADD CONSTRAINT FK_3098B4144235D463 FOREIGN KEY (technology_id) REFERENCES technology (id)');
        $this->addSql('ALTER TABLE technology_usage ADD CONSTRAINT FK_3098B41484A0A3ED FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D119EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84419EB6921');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8442FC0CB0F');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8449E45C554');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9464E68B');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9A76ED395');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9C5BF4C20');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9DCF7B613');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB95200282E');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9BF396750');
        $this->addSql('ALTER TABLE email_verification DROP FOREIGN KEY FK_FE22358F675F31B');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFDCF7B613');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFBF396750');
        $this->addSql('ALTER TABLE image_realisation DROP FOREIGN KEY FK_25732A80B685E551');
        $this->addSql('ALTER TABLE password_reset DROP FOREIGN KEY FK_B1017252F675F31B');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2FC0CB0F');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD809EE01F');
        $this->addSql('ALTER TABLE prestation_tag DROP FOREIGN KEY FK_4C50F389E45C554');
        $this->addSql('ALTER TABLE prestation_tag DROP FOREIGN KEY FK_4C50F38BAD26311');
        $this->addSql('ALTER TABLE technology_requirement DROP FOREIGN KEY FK_FA5618B015B5A9D8');
        $this->addSql('ALTER TABLE technology_requirement DROP FOREIGN KEY FK_FA5618B0C50F957');
        $this->addSql('ALTER TABLE technology_usage DROP FOREIGN KEY FK_3098B4144235D463');
        $this->addSql('ALTER TABLE technology_usage DROP FOREIGN KEY FK_3098B41484A0A3ED');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D119EB6921');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE holiday');
        $this->addSql('DROP TABLE image_realisation');
        $this->addSql('DROP TABLE `option`');
        $this->addSql('DROP TABLE password_reset');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE prestation_tag');
        $this->addSql('DROP TABLE realisation');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE technology');
        $this->addSql('DROP TABLE technology_requirement');
        $this->addSql('DROP TABLE technology_usage');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
