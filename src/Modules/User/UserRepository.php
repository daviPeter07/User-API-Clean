<?php

namespace App\Modules\User;

use App\Database\Connect;
use PDO;

class UserRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = Connect::getInstance();
  }

  public function create(array $data): void
  {
    $query = 'INSERT INTO users (id, name, email, password) VALUES (:id, :name, :email, :password)';

    $stmt = $this->db->prepare($query);

    $stmt->execute([
      ':id'       => $data['id'],
      ':name'     => $data['name'],
      ':email'    => $data['email'],
      ':password' => $data['password'],
    ]);
  }

  public function findByEmail(string $email): ?array
  {
    $query = 'SELECT * FROM users WHERE email = :email LIMIT 1';
    $stmt = $this->db->prepare($query);
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch();

    return $user ?: null;
  }

  public function count(): int
  {
    return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
  }

  /**
   * @return list<array<string, mixed>>
   */
  public function findAll(int $limit, int $offset): array
  {
    $sql = 'SELECT id, name, email, created_at, updated_at FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function findById(string $id): ?array
  {
    $query = 'SELECT id, name, email, created_at, updated_at FROM users WHERE id = :id LIMIT 1';
    $stmt = $this->db->prepare($query);
    $stmt->execute([':id' => $id]);

    $user = $stmt->fetch();

    return $user ?: null;
  }

  /**
   * @param array<string, string> $fields apenas name, email ou password (já processados)
   */
  public function update(string $id, array $fields): void
  {
    if ($fields === []) {
      return;
    }

    $sets = [];
    $params = [':id' => $id];
    foreach ($fields as $column => $value) {
      $sets[] = "{$column} = :{$column}";
      $params[":{$column}"] = $value;
    }

    $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
  }

  public function delete(string $id): bool
  {
    $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);

    return $stmt->rowCount() > 0;
  }
}
