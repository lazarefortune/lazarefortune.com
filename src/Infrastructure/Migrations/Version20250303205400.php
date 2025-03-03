<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303205400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE newsletter_subscriber ADD unsubscribe_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_401562C3E0674361 ON newsletter_subscriber (unsubscribe_token)');
        $this->addSql('ALTER TABLE user ADD unsubscribe_newsletter_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6491D8305E ON user (unsubscribe_newsletter_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_401562C3E0674361 ON newsletter_subscriber');
        $this->addSql('ALTER TABLE newsletter_subscriber DROP unsubscribe_token');
        $this->addSql('DROP INDEX UNIQ_8D93D6491D8305E ON user');
        $this->addSql('ALTER TABLE user DROP unsubscribe_newsletter_token');
    }
}
