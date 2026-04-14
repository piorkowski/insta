<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260414000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_integrations table, unique constraint on likes, and messenger_messages table';
    }

    public function up(Schema $schema): void
    {
        // User integrations table for external service connections
        $this->addSql('CREATE TABLE user_integrations (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            provider VARCHAR(50) NOT NULL,
            credential_type VARCHAR(50) NOT NULL,
            credential_value VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            CONSTRAINT fk_user_integrations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )');

        $this->addSql('CREATE UNIQUE INDEX unique_user_provider ON user_integrations(user_id, provider)');
        $this->addSql('CREATE INDEX idx_user_integrations_user_id ON user_integrations(user_id)');

        // Fix: add unique constraint on likes to prevent duplicate likes
        $this->addSql('DELETE FROM likes a USING likes b WHERE a.id > b.id AND a.user_id = b.user_id AND a.photo_id = b.photo_id');
        $this->addSql('CREATE UNIQUE INDEX unique_user_photo_like ON likes(user_id, photo_id)');

        // Messenger messages table for async event processing
        $this->addSql('CREATE TABLE messenger_messages (
            id BIGSERIAL PRIMARY KEY,
            body TEXT NOT NULL,
            headers TEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            available_at TIMESTAMP NOT NULL DEFAULT NOW(),
            delivered_at TIMESTAMP DEFAULT NULL
        )');

        $this->addSql('CREATE INDEX idx_messenger_messages_queue_name ON messenger_messages(queue_name)');
        $this->addSql('CREATE INDEX idx_messenger_messages_available_at ON messenger_messages(available_at)');
        $this->addSql('CREATE INDEX idx_messenger_messages_delivered_at ON messenger_messages(delivered_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX unique_user_photo_like');
        $this->addSql('DROP TABLE user_integrations');
    }
}
