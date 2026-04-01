<?php

namespace App\Modules\User;

use App\Modules\User\DTO\CreateUserDTO;
use Ramsey\Uuid\Uuid;

class UserService
{
  private UserRepository $repository;

  public function __construct(UserRepository $repository)
  {
    $this->repository = $repository;
  }

  public function createUser(CreateUserDTO $dto): array
  {
    $id = Uuid::uuid4()->toString();

    $hashedPassword = password_hash($dto->password, PASSWORD_BCRYPT);

    $userData = [
      'id'       => $id,
      'name'     => $dto->name,
      'email'    => $dto->email,
      'password' => $hashedPassword
    ];

    $this->repository->create($userData);

    return [
      'id'    => $id,
      'name'  => $dto->name,
      'email' => $dto->email,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
    ];
  }
}
