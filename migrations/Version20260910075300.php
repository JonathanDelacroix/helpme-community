<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910075300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api_token column to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD api_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP api_token');
    }
}
