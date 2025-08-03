<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renames the "Draft" cycle to "Variants".
 */
final class Version20190508061240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renames the "Draft" cycle to "Variants".';
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql("UPDATE cycle SET code = 'variants', name = 'Variants' WHERE code = 'draft'");
    }

    /**
     * @inheritdoc
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql("UPDATE cycle SET code = 'draft', name = 'Draft Sets' WHERE code = 'variants'");
    }
}
