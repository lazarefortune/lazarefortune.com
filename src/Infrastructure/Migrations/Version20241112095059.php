<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112095059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `option` MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON `option`');
        $this->addSql('ALTER TABLE `option` ADD `key` VARCHAR(255) NOT NULL, DROP id, DROP label, DROP name, DROP type, CHANGE value value LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX key_idx ON `option` (`key`)');
        $this->addSql('ALTER TABLE `option` ADD PRIMARY KEY (`key`)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX key_idx ON `option`');
        $this->addSql('ALTER TABLE `option` ADD id INT AUTO_INCREMENT NOT NULL, ADD name VARCHAR(255) NOT NULL, ADD type VARCHAR(255) DEFAULT NULL, CHANGE value value VARCHAR(255) DEFAULT NULL, CHANGE `key` label VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
