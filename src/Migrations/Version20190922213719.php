<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds UUID column to deck table.
 */
final class Version20190922213719 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds UUID column to deck table.';
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE deck ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FAC3637D17F50A6 ON deck (uuid)');
    }

    /**
     * @inheritdoc
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX UNIQ_4FAC3637D17F50A6 ON deck');
        $this->addSql('ALTER TABLE deck DROP uuid');
    }
}
