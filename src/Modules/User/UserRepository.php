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
    $query = "INSERT INTO users (id, name, email, password) VALUES (:id, :name, :email, :password)";

    $stmt = $this->db->prepare($query);

    $stmt->execute([
      ':id'       => $data['id'],
      ':name'     => $data['name'],
      ':email'    => $data['email'],
      ':password' => $data['password']
    ]);
  }

  public function findByEmail(string $email): ?array
  {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $this->db->prepare($query);
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch();

    // Se achar o usuário, retorna o array. Se não achar, retorna null.
    return $user ?: null;
  }
}
