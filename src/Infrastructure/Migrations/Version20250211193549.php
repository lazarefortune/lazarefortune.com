<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211193549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE plan (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, duration INT NOT NULL, stripe_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, plan_id INT NOT NULL, user_id INT NOT NULL, state SMALLINT NOT NULL, next_payment DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', stripe_id VARCHAR(255) DEFAULT NULL, INDEX IDX_A3C664D3E899029B (plan_id), INDEX IDX_A3C664D3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, duration INT NOT NULL, price DOUBLE PRECISION NOT NULL, tax DOUBLE PRECISION NOT NULL, method VARCHAR(255) NOT NULL, method_ref VARCHAR(255) DEFAULT NULL, refunded TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, country_code VARCHAR(255) DEFAULT NULL, fee DOUBLE PRECISION DEFAULT \'0\' NOT NULL, INDEX IDX_723705D1F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD premium_end DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3E899029B');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F675F31B');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('ALTER TABLE user DROP premium_end');
    }
}
