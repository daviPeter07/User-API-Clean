<?php

namespace App\Modules\User;

use Rakit\Validation\Validator;

class UserValidator
{
  //instancia do objeto do rakit
  private Validator $validator;

  //magic method
  //aloca objeto na memoria sendo possivel acessar da variavel $validator
  public function __construct()
  {
    $this->validator = new Validator();
  }

  public function validateCreate(array $data): array
  {
    //aponta pra objeto, pegando o metodo make do Rakit
    $validation = $this->validator->make($data, [
      'name'      => "required|min:3",
      'email'     => "required|email",
      'password'  => "required|min:8"
    ]);

    //aponta pra metodo setMessages do rakit
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
