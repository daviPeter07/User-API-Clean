<?php

namespace App\Database;

use App\Http\JsonErrorHandler;
use PDO;
use PDOException;

class Connect
{
  private static ?PDO $instance = null;

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      try {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

        self::$instance = new PDO($dsn, $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
      } catch (PDOException $e) {
        self::abortOnConnectionFailure($e);
      }
    }

    return self::$instance;
  }

  private static function abortOnConnectionFailure(PDOException $e): void
  {
    $handler = new JsonErrorHandler();
    $mensagem = "Não foi possível conectar ao banco de dados.";

    $appDebug = isset($_ENV['APP_DEBUG']) ? (string) $_ENV['APP_DEBUG'] : '';
    $debugLigado = ($appDebug === 'true' || $appDebug === '1');

    if ($debugLigado) {
      $handler->send(503, $mensagem, ['motivo' => $e->getMessage()]);
    } else {
      $handler->send(503, $mensagem);
    }

    exit(1);
  }
}
