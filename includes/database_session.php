<?php
declare(strict_types=1);

// Oturumlar geçici sunucu diskine değil veritabanına yazılır.
// Ayrı bağlantıdaki transaction kilidi eşzamanlı isteklerin veriyi ezmesini önler.
final class DatabaseSession implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private PDO $connection;
    public function __construct() { db(); $this->connection = create_connection(); }
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool
    {
        if ($this->connection->inTransaction()) $this->connection->commit();
        return true;
    }
    public function validateId(string $id): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM sessions WHERE id = ? AND expires_at > ?');
        $query->execute([hash('sha256', $id), time()]);
        return (bool) $query->fetchColumn();
    }
    public function read(string $id): string|false
    {
        $key = hash('sha256', $id);
        $this->connection->beginTransaction();
        $query = $this->connection->prepare('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))');
        $query->execute([$key]);
        $query = $this->connection->prepare('SELECT data FROM sessions WHERE id = ? AND expires_at > ?');
        $query->execute([$key, time()]);
        return $query->fetchColumn() ?: '';
    }
    public function write(string $id, string $data): bool
    {
        $query = $this->connection->prepare('INSERT INTO sessions(id,data,expires_at) VALUES(?,?,?) ON CONFLICT(id) DO UPDATE SET data=EXCLUDED.data, expires_at=EXCLUDED.expires_at');
        return $query->execute([hash('sha256', $id), $data, time() + 7200]);
    }
    public function updateTimestamp(string $id, string $data): bool { return $this->write($id, $data); }
    public function destroy(string $id): bool
    {
        $query = $this->connection->prepare('DELETE FROM sessions WHERE id=?');
        return $query->execute([hash('sha256', $id)]);
    }
    public function gc(int $max_lifetime): int|false
    {
        $query = $this->connection->prepare('DELETE FROM sessions WHERE expires_at < ?');
        $query->execute([time()]);
        return $query->rowCount();
    }
}
