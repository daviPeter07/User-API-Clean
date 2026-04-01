<?php

namespace App\Modules\User\DTO;

class CreateUserDTO {
  public function __construct(
    public string $name,
    public string $email,
    public string $password,
  ) {}
}