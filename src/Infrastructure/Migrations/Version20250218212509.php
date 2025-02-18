<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218212509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9284A0A3ED');
        $this->addSql('DROP INDEX IDX_A412FA9284A0A3ED ON quiz');
        $this->addSql('ALTER TABLE quiz CHANGE content_id target_content_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92B64C7B7C FOREIGN KEY (target_content_id) REFERENCES content (id)');
        $this->addSql('CREATE INDEX IDX_A412FA92B64C7B7C ON quiz (target_content_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92B64C7B7C');
        $this->addSql('DROP INDEX IDX_A412FA92B64C7B7C ON quiz');
        $this->addSql('ALTER TABLE quiz CHANGE target_content_id content_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9284A0A3ED FOREIGN KEY (content_id) REFERENCES content (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A412FA9284A0A3ED ON quiz (content_id)');
    }
}
