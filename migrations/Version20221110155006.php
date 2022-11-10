<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ensure that decks with descendants can be deleted by breaking the ancestral chain.
 */
final class Version20221110155006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure that decks with descendants can be deleted by breaking the ancestral chain.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC363763513C9A');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC363763513C9A FOREIGN KEY (parent_deck_id) REFERENCES deck (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC363763513C9A');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC363763513C9A FOREIGN KEY (parent_deck_id) REFERENCES deck (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
