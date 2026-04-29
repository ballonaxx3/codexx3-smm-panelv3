<?php
class Database {
    private ?PDO $pdo = null;

    public function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'codexx3_smm';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            $this->log('DB connection error: '.$e->getMessage());
            http_response_code(500);
            exit('Error de conexión a base de datos.');
        }
    }

    public function pdo(): PDO { return $this->pdo; }

    public function query(string $sql, array $params = []): PDOStatement|false {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Throwable $e) {
            $this->log('Query error: '.$e->getMessage().' SQL: '.$sql);
            return false;
        }
    }

    private function log(string $msg): void {
        $file = __DIR__.'/../../logs/database.log';
        @file_put_contents($file, '['.date('Y-m-d H:i:s').'] '.$msg.PHP_EOL, FILE_APPEND);
    }
}
