<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401102515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_item (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, commande_id INT NOT NULL, plat_id INT NOT NULL, INDEX IDX_747724FD82EA2E54 (commande_id), INDEX IDX_747724FDD73DB560 (plat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDD73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id)');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `FK_6EEAA67DCCD7E912`');
        $this->addSql('DROP INDEX IDX_6EEAA67DCCD7E912 ON commande');
        $this->addSql('ALTER TABLE commande ADD total DOUBLE PRECISION NOT NULL, DROP date_prestation, DROP heure_prestation, DROP adresse_livraison, DROP ville_livraison, DROP distance_km, DROP prix_livraison, DROP nb_personnes, DROP prix_total, DROP menu_id, CHANGE date_commande date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE plat ADD prix DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD82EA2E54');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDD73DB560');
        $this->addSql('DROP TABLE commande_item');
        $this->addSql('ALTER TABLE commande ADD date_prestation DATE NOT NULL, ADD heure_prestation TIME NOT NULL, ADD adresse_livraison LONGTEXT NOT NULL, ADD ville_livraison VARCHAR(100) NOT NULL, ADD prix_livraison DOUBLE PRECISION NOT NULL, ADD nb_personnes INT NOT NULL, ADD prix_total DOUBLE PRECISION NOT NULL, ADD menu_id INT NOT NULL, CHANGE date date_commande DATETIME NOT NULL, CHANGE total distance_km DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `FK_6EEAA67DCCD7E912` FOREIGN KEY (menu_id) REFERENCES menu (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6EEAA67DCCD7E912 ON commande (menu_id)');
        $this->addSql('ALTER TABLE plat DROP prix');
    }
}
