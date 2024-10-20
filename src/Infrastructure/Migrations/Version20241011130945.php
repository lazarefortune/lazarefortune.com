<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011130945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84419EB6921');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8442FC0CB0F');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8449E45C554');
        $this->addSql('ALTER TABLE image_realisation DROP FOREIGN KEY FK_25732A80B685E551');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2FC0CB0F');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD809EE01F');
        $this->addSql('ALTER TABLE prestation_tag DROP FOREIGN KEY FK_4C50F389E45C554');
        $this->addSql('ALTER TABLE prestation_tag DROP FOREIGN KEY FK_4C50F38BAD26311');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D119EB6921');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE holiday');
        $this->addSql('DROP TABLE image_realisation');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE prestation');
        $this->addSql('DROP TABLE prestation_tag');
        $this->addSql('DROP TABLE realisation');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('ALTER TABLE progress ADD last_login DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, transaction_id INT DEFAULT NULL, prestation_id INT NOT NULL, comment LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8_unicode_ci`, nb_adults INT NOT NULL, nb_children INT NOT NULL, date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, sub_total INT DEFAULT NULL, total INT DEFAULT NULL, amount_paid INT DEFAULT NULL, applied_discount INT DEFAULT NULL, access_token VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8_unicode_ci`, is_paid TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_FE38F84419EB6921 (client_id), INDEX IDX_FE38F8442FC0CB0F (transaction_id), INDEX IDX_FE38F8449E45C554 (prestation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, is_active TINYINT(1) NOT NULL, description LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE holiday (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, start_date DATE NOT NULL, end_date DATE NOT NULL, UNIQUE INDEX UNIQ_DC9AB2342B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE image_realisation (id INT AUTO_INCREMENT NOT NULL, realisation_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_25732A80B685E551 (realisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, transaction_id INT NOT NULL, amount INT NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, session_id VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8_unicode_ci`, payment_method VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_6D28840D2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, is_available_to_client TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, category_prestation_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8_unicode_ci`, status VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8_unicode_ci`, price INT DEFAULT NULL, duration TIME DEFAULT NULL, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, avalaible_space_per_prestation INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, consider_children_for_price TINYINT(1) DEFAULT NULL, children_age_range INT DEFAULT NULL, children_price_percentage DOUBLE PRECISION DEFAULT NULL, INDEX IDX_51C88FAD809EE01F (category_prestation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE prestation_tag (prestation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_4C50F389E45C554 (prestation_id), INDEX IDX_4C50F38BAD26311 (tag_id), PRIMARY KEY(prestation_id, tag_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE realisation (id INT AUTO_INCREMENT NOT NULL, online TINYINT(1) NOT NULL, date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, amount INT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, INDEX IDX_723705D119EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84419EB6921 FOREIGN KEY (client_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8442FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8449E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE image_realisation ADD CONSTRAINT FK_25732A80B685E551 FOREIGN KEY (realisation_id) REFERENCES realisation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD809EE01F FOREIGN KEY (category_prestation_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE prestation_tag ADD CONSTRAINT FK_4C50F389E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prestation_tag ADD CONSTRAINT FK_4C50F38BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D119EB6921 FOREIGN KEY (client_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE progress DROP last_login');
    }
}
