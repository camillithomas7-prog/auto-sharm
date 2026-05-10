<?php
function cfg(?string $key = null) {
    static $config = null;
    if ($config === null) {
        $path = __DIR__ . '/../config.php';
        if (!file_exists($path)) { http_response_code(500); die('Config mancante: copia config.sample.php in config.php'); }
        $config = require $path;
    }
    if ($key === null) return $config;
    $parts = explode('.', $key);
    $v = $config;
    foreach ($parts as $p) { if (!isset($v[$p])) return null; $v = $v[$p]; }
    return $v;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = cfg('db');
    $driver = $c['driver'] ?? 'mysql';
    try {
        if ($driver === 'sqlite') {
            $dir = dirname($c['path']);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $pdo = new PDO('sqlite:' . $c['path'], null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA journal_mode = WAL');
        } else {
            $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}";
            $pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            try {
                $tz = cfg('site.timezone') ?: 'Africa/Cairo';
                $offset = (new DateTime('now', new DateTimeZone($tz)))->format('P');
                $pdo->exec("SET time_zone = '$offset'");
            } catch (Throwable $e) {}
        }
    } catch (Throwable $e) {
        http_response_code(500);
        die('Errore connessione DB: ' . htmlspecialchars($e->getMessage()));
    }
    return $pdo;
}

function dbDriver(): string { return cfg('db.driver') ?? 'mysql'; }

function q(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function rows(string $sql, array $params = []): array { return q($sql, $params)->fetchAll(); }
function row(string $sql, array $params = []): ?array { $r = q($sql, $params)->fetch(); return $r ?: null; }
function val(string $sql, array $params = []) { $r = q($sql, $params)->fetch(PDO::FETCH_NUM); return $r ? $r[0] : null; }

function newId(): string {
    return bin2hex(random_bytes(12));
}

function tableExists(string $name): bool {
    try { q("SELECT 1 FROM \"$name\" LIMIT 1"); return true; } catch (Throwable $e) { return false; }
}
