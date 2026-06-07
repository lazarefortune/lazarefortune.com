<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260607175842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create content_progress table for user content tracking';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_progress (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, content_id INT NOT NULL, percent SMALLINT NOT NULL, last_position_seconds INT DEFAULT NULL, completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_70B618A9A76ED395 (user_id), INDEX IDX_70B618A984A0A3ED (content_id), INDEX idx_progress_user_updated_at (user_id, updated_at), UNIQUE INDEX uniq_user_content_progress (user_id, content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content_progress ADD CONSTRAINT FK_70B618A9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_progress ADD CONSTRAINT FK_70B618A984A0A3ED FOREIGN KEY (content_id) REFERENCES contents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content_progress DROP FOREIGN KEY FK_70B618A9A76ED395');
        $this->addSql('ALTER TABLE content_progress DROP FOREIGN KEY FK_70B618A984A0A3ED');
        $this->addSql('DROP TABLE content_progress');
    }
}
