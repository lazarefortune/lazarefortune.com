<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301215845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP INDEX UNIQ_169E6FB9C5BF4C20, ADD INDEX IDX_169E6FB9C5BF4C20 (youtube_thumbnail_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP INDEX IDX_169E6FB9C5BF4C20, ADD UNIQUE INDEX UNIQ_169E6FB9C5BF4C20 (youtube_thumbnail_id)');
    }
}
