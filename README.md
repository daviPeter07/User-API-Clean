# API — Usuários

API REST de usuários em **PHP 8+** sem framework full-stack: rotas com [Bramus Router](https://github.com/bramus/router), MySQL via PDO, validação com Rakit, UUID com Ramsey, migrações com Phinx e documentação **OpenAPI + Swagger UI**.

## Requisitos

- PHP 8.1 ou superior (extensões: `pdo_mysql`, `json`, `mbstring`)
- Composer
- MySQL 5.7+ ou MariaDB equivalente

## Instalação

```bash
git clone https://github.com/daviPeter07/User-API-Clean
cd User-API-Clean
composer install
```

Crie um arquivo `.env` na raiz do projeto (ao lado de `composer.json`) com:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha

# opcional: em desenvolvimento, expõe mensagem da exceção em erros HTTP 500
APP_DEBUG=true
```

Crie o banco vazio no MySQL e rode as migrações:

```bash
vendor\bin\phinx migrate
```

No Linux/macOS use `vendor/bin/phinx migrate`.

## Executar em desenvolvimento

```bash
composer run dev
```

O servidor embutido do PHP sobe em `http://localhost:8000`, com suporte a arquivos estáticos (Swagger) e à API via `public/index.php`.

## Documentação da API (Swagger)

- Interface: [http://localhost:8000/swagger/index.html](http://localhost:8000/swagger/index.html)
- Atalhos: `GET /docs` ou `GET /swagger` redirecionam para a UI
- Especificação OpenAPI: `public/swagger/openapi.yaml` (URL: `/swagger/openapi.yaml`)

Na raiz da API, `GET /` retorna um JSON com links para Swagger e OpenAPI.

## Rotas principais

| Método          | Caminho             | Descrição                                  |
| --------------- | ------------------- | ------------------------------------------ |
| `GET`           | `/`                 | Status da API + links da documentação      |
| `GET`           | `/test-db`          | Teste rápido de conexão com o banco        |
| `GET`           | `/api/users`        | Lista usuários (query: `page`, `per_page`) |
| `POST`          | `/api/users`        | Cria usuário                               |
| `GET`           | `/api/users/{uuid}` | Detalhe do usuário                         |
| `PUT` / `PATCH` | `/api/users/{uuid}` | Atualização parcial                        |
| `DELETE`        | `/api/users/{uuid}` | Remove usuário                             |

Corpo JSON para criação: `name` (mín. 3), `email`, `password` (mín. 8). Respostas de erro seguem o formato `{ "erro": { "codigo_http", "mensagem", "detalhes" } }` em português.

## Estrutura do projeto (resumo)

```
public/
  index.php                 # Front controller (autoload, .env, CORS, rotas)
  php-built-in-server.php   # Só para `composer run dev` (estático + app)
  swagger/                  # Swagger UI + openapi.yaml
src/
  Http/                     # Rotas da aplicação, JsonErrorHandler
  Modules/User/             # Controller, Service, Repository, Validator, DTOs, UserRoute
  Database/                 # Conexão e migrações Phinx
```

O ponto central de registro das rotas HTTP da aplicação é `src/Http/routes.php`. Rotas do módulo usuário ficam em `src/Modules/User/UserRoute.php`.

## Testes

```bash
composer test
```

## Produção / outro servidor

Em **Apache** ou **Nginx**, aponte o document root para `public/` e encaminhe requisições que não forem arquivos existentes para `public/index.php` (equivalente ao que `php-built-in-server.php` faz no ambiente de desenvolvimento).

## Autoria

Projeto feito por **Davi Peterson**. Código e histórico no GitHub: [**daviPeter07/User-API-Clean**](https://github.com/daviPeter07/User-API-Clean). Perfil: [@daviPeter07](https://github.com/daviPeter07).
