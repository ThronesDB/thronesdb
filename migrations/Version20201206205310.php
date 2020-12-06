<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds restriction table to schema.
 */
final class Version20201206205310 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds restriction table to schema.';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE restriction (code VARCHAR(20) NOT NULL, effective_on DATETIME NOT NULL, issuer VARCHAR(50) NOT NULL, card_set VARCHAR(20) NOT NULL, contents JSON NOT NULL, active TINYINT(1) NOT NULL, version VARCHAR(20) NOT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET UTF8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE restriction');
    }
}
