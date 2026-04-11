<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409204356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `FK_6EEAA67DFB88E14F`');
        $this->addSql('DROP INDEX IDX_6EEAA67DFB88E14F ON commande');
        $this->addSql('ALTER TABLE commande DROP utilisateur_id, DROP menu_id, CHANGE date created_at DATETIME NOT NULL, CHANGE statut_actuel status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY `FK_747724FDD73DB560`');
        $this->addSql('DROP INDEX IDX_747724FDD73DB560 ON commande_item');
        $this->addSql('ALTER TABLE commande_item CHANGE plat_id menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_747724FDCCD7E912 ON commande_item (menu_id)');
        $this->addSql('ALTER TABLE menu ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande ADD utilisateur_id INT NOT NULL, ADD menu_id INT NOT NULL, CHANGE created_at date DATETIME NOT NULL, CHANGE status statut_actuel VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `FK_6EEAA67DFB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6EEAA67DFB88E14F ON commande (utilisateur_id)');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDCCD7E912');
        $this->addSql('DROP INDEX IDX_747724FDCCD7E912 ON commande_item');
        $this->addSql('ALTER TABLE commande_item CHANGE menu_id plat_id INT NOT NULL');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT `FK_747724FDD73DB560` FOREIGN KEY (plat_id) REFERENCES plat (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_747724FDD73DB560 ON commande_item (plat_id)');
        $this->addSql('ALTER TABLE menu DROP image');
    }
}
