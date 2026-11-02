CREATE SCHEMA IF NOT EXISTS posta;
SET search_path TO posta, public;
CREATE TABLE IF NOT EXISTS users (
 id SERIAL PRIMARY KEY, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL,
 username VARCHAR(30) NOT NULL UNIQUE, email VARCHAR(80) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS messages (
 id BIGSERIAL PRIMARY KEY, sender_id INTEGER NOT NULL REFERENCES users(id), recipient_id INTEGER NOT NULL REFERENCES users(id),
 subject VARCHAR(180) NOT NULL, body TEXT NOT NULL,
 is_read SMALLINT NOT NULL DEFAULT 0, is_starred SMALLINT NOT NULL DEFAULT 0,
 deleted_by_sender SMALLINT NOT NULL DEFAULT 0, deleted_by_recipient SMALLINT NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_recipient_inbox ON messages(recipient_id,deleted_by_recipient,created_at);
CREATE INDEX IF NOT EXISTS idx_sender_sent ON messages(sender_id,deleted_by_sender,created_at);
CREATE INDEX IF NOT EXISTS idx_recipient_starred ON messages(recipient_id,is_starred);
CREATE TABLE IF NOT EXISTS attachments (
 id BIGSERIAL PRIMARY KEY, message_id BIGINT NOT NULL UNIQUE REFERENCES messages(id) ON DELETE CASCADE,
 original_name VARCHAR(180) NOT NULL, stored_name VARCHAR(64) NOT NULL UNIQUE,
 mime_type VARCHAR(80) NOT NULL, size_bytes INTEGER NOT NULL, content_base64 TEXT NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS sessions (
 id VARCHAR(64) PRIMARY KEY, data TEXT NOT NULL, expires_at BIGINT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_sessions_expiry ON sessions(expires_at);
