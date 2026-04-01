<?php

namespace App\Modules\User;

use App\Modules\User\DTO\CreateUserDTO;
use App\Modules\User\DTO\UpdateUserDTO;
use Ramsey\Uuid\Uuid;

class UserService
{
  private UserRepository $repository;

  public function __construct(UserRepository $repository)
  {
    $this->repository = $repository;
  }

  public function listUsers(int $page, int $perPage): array
  {
    $total = $this->repository->count();
    $offset = ($page - 1) * $perPage;
    $users = $this->repository->findAll($perPage, $offset);

    return [
      'data' => $users,
      'meta' => [
        'page'       => $page,
        'per_page'   => $perPage,
        'total'      => $total,
        'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
      ],
    ];
  }

  public function getUserById(string $id): ?array
  {
    return $this->repository->findById($id);
  }

  public function createUser(CreateUserDTO $dto): array
  {
    if ($this->repository->findByEmail($dto->email) !== null) {
      throw new \DomainException('E-mail já cadastrado.');
    }

    $id = Uuid::uuid4()->toString();
    $hashedPassword = password_hash($dto->password, PASSWORD_BCRYPT);

    $this->repository->create([
      'id'       => $id,
      'name'     => $dto->name,
      'email'    => $dto->email,
      'password' => $hashedPassword,
    ]);

    $row = $this->repository->findById($id);

    return $row ?? [
      'id'         => $id,
      'name'       => $dto->name,
      'email'      => $dto->email,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
    ];
  }

  public function updateUser(string $id, UpdateUserDTO $dto): ?array
  {
    $existing = $this->repository->findById($id);
    if ($existing === null) {
      return null;
    }

    $fields = [];
    if ($dto->name !== null) {
      $fields['name'] = $dto->name;
    }
    if ($dto->email !== null) {
      $other = $this->repository->findByEmail($dto->email);
      if ($other !== null && ($other['id'] ?? null) !== $id) {
        throw new \DomainException('E-mail já cadastrado.');
      }
      $fields['email'] = $dto->email;
    }
    if ($dto->password !== null) {
      $fields['password'] = password_hash($dto->password, PASSWORD_BCRYPT);
    }

    if ($fields === []) {
      return $existing;
    }

    $this->repository->update($id, $fields);

    return $this->repository->findById($id);
  }

  public function deleteUser(string $id): bool
  {
    return $this->repository->delete($id);
  }
}
