<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds "active" flag to tournament table.
 */
final class Version20190329042504 extends AbstractMigration
{
    /**
     * @inheritdDoc
     */
    public function getDescription() : string
    {
        return 'Adds "active" flag to tournament table.';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament ADD active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament DROP active');
    }
}
