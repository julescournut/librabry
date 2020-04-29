<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200429090157 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE detail_livraison (id INT AUTO_INCREMENT NOT NULL, livre_id INT NOT NULL, livraison_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_B7FB4AAA37D925CB (livre_id), INDEX IDX_B7FB4AAA8E54FB25 (livraison_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, adresse_id INT NOT NULL, utilisateur_id INT NOT NULL, date_commande DATE DEFAULT NULL, date_livraison DATE DEFAULT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_A60C9F1F4DE7DC5C (adresse_id), INDEX IDX_A60C9F1FFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE detail_livraison ADD CONSTRAINT FK_B7FB4AAA37D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE detail_livraison ADD CONSTRAINT FK_B7FB4AAA8E54FB25 FOREIGN KEY (livraison_id) REFERENCES livraison (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE detail_livraison DROP FOREIGN KEY FK_B7FB4AAA8E54FB25');
        $this->addSql('DROP TABLE detail_livraison');
        $this->addSql('DROP TABLE livraison');
    }
}
