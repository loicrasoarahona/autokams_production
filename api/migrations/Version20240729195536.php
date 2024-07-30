<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240729195536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE versement (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, daty DATETIME NOT NULL, INDEX IDX_716E936719EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE versement ADD CONSTRAINT FK_716E936719EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE paiement ADD versement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EDBBF8D62 FOREIGN KEY (versement_id) REFERENCES versement (id)');
        $this->addSql('CREATE INDEX IDX_B1DC7A1EDBBF8D62 ON paiement (versement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EDBBF8D62');
        $this->addSql('ALTER TABLE versement DROP FOREIGN KEY FK_716E936719EB6921');
        $this->addSql('DROP TABLE versement');
        $this->addSql('DROP INDEX IDX_B1DC7A1EDBBF8D62 ON paiement');
        $this->addSql('ALTER TABLE paiement DROP versement_id');
    }
}
