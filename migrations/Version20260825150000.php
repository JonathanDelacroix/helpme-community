<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le suivi du statut de paiement des dons (pending/paid/cancelled)
 * et l'identifiant de session Stripe associe, afin de ne plus compter
 * comme "collectes" des dons dont le paiement n'a jamais ete confirme.
 */
final class Version20260825150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute donation.status et donation.stripe_session_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE donation ADD status VARCHAR(20) NOT NULL DEFAULT 'pending', ADD stripe_session_id VARCHAR(255) DEFAULT NULL");
        // Les dons existants sans statut connu sont conserves en 'pending' par defaut ci-dessus.
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donation DROP status, DROP stripe_session_id');
    }
}
