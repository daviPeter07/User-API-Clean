<?php

namespace App\Modules\User;

use Rakit\Validation\Validator;
use InvalidArgumentException;

class UserValidator
{
  private Validator $validator;

  public function __construct()
  {
    $this->validator = new Validator();
  }

  public function validateCreate(array $data): array
  {
    $validation = $this->validator->make($data, [
      'name' => "required|min:3",
      'email' => "required|email",
      'password' => "required|min:8"
    ]);

    $validation->setMessages([
      'required' => 'O campo :attribute é obrigatório.',
      'email'    => 'O campo :attribute deve ser um email válido.',
      'min'      => 'O campo :attribute deve ter no mínimo :min caracteres.'
    ]);

    $validation->validate();

    if ($validation->fails()) {
      return [
        'success' => false,
        'errors'  => $validation->errors()->firstOfAll()
      ];
    }
    
    return [
      'success' => true,
      'data'    => $validation->getValidData()
    ];
  }
}
