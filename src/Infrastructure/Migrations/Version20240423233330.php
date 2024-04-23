<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423233330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realisation DROP amount, DROP boolean, DROP duration, CHANGE is_online online TINYINT(1) NOT NULL, CHANGE date_realisation date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realisation ADD amount INT DEFAULT NULL, ADD boolean TINYINT(1) DEFAULT NULL, ADD duration TIME DEFAULT NULL, CHANGE online is_online TINYINT(1) NOT NULL, CHANGE date date_realisation DATETIME DEFAULT NULL');
    }
}
