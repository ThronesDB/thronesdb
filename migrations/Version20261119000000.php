<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261119000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add an audit trail for card review editing.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE review_edit_history (id INT AUTO_INCREMENT NOT NULL, review_id INT NOT NULL, author_user_id INT NOT NULL, editor_user_id INT NOT NULL, edited_at DATETIME NOT NULL, previous_text_md LONGTEXT NOT NULL, new_text_md LONGTEXT NOT NULL, INDEX review_edit_history_review_idx (review_id), INDEX review_edit_history_editor_idx (editor_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE review_edit_history');
    }
}
