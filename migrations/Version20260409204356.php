<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409204356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne image dans menu';
    }

    public function up(Schema $schema): void
    {
        // On ne touche plus à commande ni commande_item
        // car ces colonnes/clefs n'existent plus dans ta base actuelle.

        $this->addSql('ALTER TABLE menu ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Suppression inverse
        $this->addSql('ALTER TABLE menu DROP image');
    }
}