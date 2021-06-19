<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210619152947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (`id` BIGINT NOT NULL AUTO_INCREMENT, `email` VARCHAR(100) NOT NULL UNIQUE , `lastname` VARCHAR(100), `firstname` VARCHAR(100), `fiscal_code` VARCHAR(30), `description` VARCHAR(255), `last_access_at` DATETIME, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP, `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`, `email`));');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user;');
    }
}
