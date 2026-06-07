<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260607174716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create V3 editorial domain tables (Content, Video, Article, Playlist, Tag)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE articles (id INT NOT NULL, body LONGTEXT NOT NULL, reading_time_minutes INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content_tags (id INT AUTO_INCREMENT NOT NULL, content_id INT NOT NULL, tag_id INT NOT NULL, is_primary TINYINT(1) NOT NULL, INDEX IDX_A2DE79E384A0A3ED (content_id), INDEX IDX_A2DE79E3BAD26311 (tag_id), UNIQUE INDEX uniq_content_tag (content_id, tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contents (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, replaced_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, level VARCHAR(255) DEFAULT NULL, seo_title VARCHAR(255) DEFAULT NULL, seo_description LONGTEXT DEFAULT NULL, cover_image_path VARCHAR(512) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, visibility VARCHAR(255) NOT NULL, scheduled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', dtype VARCHAR(255) NOT NULL, INDEX IDX_B4FA11779AC69B54 (replaced_by_id), INDEX idx_content_status_published_at (status, published_at), INDEX idx_content_author (author_id), UNIQUE INDEX uniq_content_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist_chapters (id INT AUTO_INCREMENT NOT NULL, playlist_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3AFC851C6BBD148 (playlist_id), UNIQUE INDEX uniq_playlist_chapter_position (playlist_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist_items (id INT AUTO_INCREMENT NOT NULL, chapter_id INT NOT NULL, content_id INT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4202F00E579F4768 (chapter_id), INDEX IDX_4202F00E84A0A3ED (content_id), UNIQUE INDEX uniq_chapter_item_position (chapter_id, position), UNIQUE INDEX uniq_chapter_content (chapter_id, content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlists (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, short_description LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, level VARCHAR(255) DEFAULT NULL, seo_title VARCHAR(255) DEFAULT NULL, seo_description LONGTEXT DEFAULT NULL, cover_image_path VARCHAR(512) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, visibility VARCHAR(255) NOT NULL, scheduled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_playlist_status_published_at (status, published_at), INDEX idx_playlist_author (author_id), UNIQUE INDEX uniq_playlist_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_tag_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_sources (id INT AUTO_INCREMENT NOT NULL, video_id INT NOT NULL, provider VARCHAR(255) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, url VARCHAR(2048) DEFAULT NULL, visibility VARCHAR(255) NOT NULL, duration_seconds INT DEFAULT NULL, thumbnail_url VARCHAR(2048) DEFAULT NULL, metadata JSON DEFAULT NULL, is_primary TINYINT(1) NOT NULL, last_synced_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_37B08C5F29C1004E (video_id), UNIQUE INDEX uniq_video_provider_external_id (provider, external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE videos (id INT NOT NULL, description LONGTEXT NOT NULL, duration_seconds INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD3168BF396750 FOREIGN KEY (id) REFERENCES contents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_tags ADD CONSTRAINT FK_A2DE79E384A0A3ED FOREIGN KEY (content_id) REFERENCES contents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_tags ADD CONSTRAINT FK_A2DE79E3BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contents ADD CONSTRAINT FK_B4FA1177F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contents ADD CONSTRAINT FK_B4FA11779AC69B54 FOREIGN KEY (replaced_by_id) REFERENCES contents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE playlist_chapters ADD CONSTRAINT FK_3AFC851C6BBD148 FOREIGN KEY (playlist_id) REFERENCES playlists (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playlist_items ADD CONSTRAINT FK_4202F00E579F4768 FOREIGN KEY (chapter_id) REFERENCES playlist_chapters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playlist_items ADD CONSTRAINT FK_4202F00E84A0A3ED FOREIGN KEY (content_id) REFERENCES contents (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE playlists ADD CONSTRAINT FK_5E06116FF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE video_sources ADD CONSTRAINT FK_37B08C5F29C1004E FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE videos ADD CONSTRAINT FK_29AA6432BF396750 FOREIGN KEY (id) REFERENCES contents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD3168BF396750');
        $this->addSql('ALTER TABLE content_tags DROP FOREIGN KEY FK_A2DE79E384A0A3ED');
        $this->addSql('ALTER TABLE content_tags DROP FOREIGN KEY FK_A2DE79E3BAD26311');
        $this->addSql('ALTER TABLE contents DROP FOREIGN KEY FK_B4FA1177F675F31B');
        $this->addSql('ALTER TABLE contents DROP FOREIGN KEY FK_B4FA11779AC69B54');
        $this->addSql('ALTER TABLE playlist_chapters DROP FOREIGN KEY FK_3AFC851C6BBD148');
        $this->addSql('ALTER TABLE playlist_items DROP FOREIGN KEY FK_4202F00E579F4768');
        $this->addSql('ALTER TABLE playlist_items DROP FOREIGN KEY FK_4202F00E84A0A3ED');
        $this->addSql('ALTER TABLE playlists DROP FOREIGN KEY FK_5E06116FF675F31B');
        $this->addSql('ALTER TABLE video_sources DROP FOREIGN KEY FK_37B08C5F29C1004E');
        $this->addSql('ALTER TABLE videos DROP FOREIGN KEY FK_29AA6432BF396750');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE content_tags');
        $this->addSql('DROP TABLE contents');
        $this->addSql('DROP TABLE playlist_chapters');
        $this->addSql('DROP TABLE playlist_items');
        $this->addSql('DROP TABLE playlists');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE video_sources');
        $this->addSql('DROP TABLE videos');
    }
}
