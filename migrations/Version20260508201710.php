<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508201710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plat_allergene DROP FOREIGN KEY `FK_6FA44BBF4646AB2`');
        $this->addSql('ALTER TABLE plat_allergene DROP FOREIGN KEY `FK_6FA44BBFD73DB560`');
        $this->addSql('DROP TABLE allergene');
        $this->addSql('DROP TABLE plat_allergene');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergene (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE plat_allergene (plat_id INT NOT NULL, allergene_id INT NOT NULL, INDEX IDX_6FA44BBF4646AB2 (allergene_id), INDEX IDX_6FA44BBFD73DB560 (plat_id), PRIMARY KEY (plat_id, allergene_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE plat_allergene ADD CONSTRAINT `FK_6FA44BBF4646AB2` FOREIGN KEY (allergene_id) REFERENCES allergene (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_allergene ADD CONSTRAINT `FK_6FA44BBFD73DB560` FOREIGN KEY (plat_id) REFERENCES plat (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
