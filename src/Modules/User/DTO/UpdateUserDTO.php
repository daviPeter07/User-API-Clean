<?php

namespace App\Modules\User\DTO;

class UpdateUserDTO
{
  public function __construct(
    public ?string $name = null,
    public ?string $email = null,
    public ?string $password = null,
  ) {}
}
