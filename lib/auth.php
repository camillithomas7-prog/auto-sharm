<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

function startSession(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'samesite' => 'Lax']);
        session_start();
    }
}

function csrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrfCheck(?string $t): void {
    startSession();
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419); die('CSRF token non valido');
    }
}

function currentUser(): ?array {
    startSession();
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) return null;
    return row('SELECT id, email, name, role FROM users WHERE id = ?', [$id]);
}

function login(string $email, string $password): bool {
    $u = row('SELECT * FROM users WHERE email = ?', [$email]);
    if (!$u || !password_verify($password, $u['password'])) return false;
    startSession();
    $_SESSION['user_id'] = $u['id'];
    return true;
}

function logout(): void {
    startSession();
    session_destroy();
}

function requireAdmin(): array {
    $u = currentUser();
    if (!$u) { header('Location: /admin/login.php'); exit; }
    return $u;
}

function ensureAdminUser(): void {
    $u = row('SELECT id FROM users WHERE email = ?', [cfg('admin_default.email')]);
    if (!$u) {
        q('INSERT INTO users (id, email, password, name, role) VALUES (?, ?, ?, ?, ?)',
            [newId(), cfg('admin_default.email'), password_hash(cfg('admin_default.password'), PASSWORD_DEFAULT), cfg('admin_default.name'), 'admin']);
    }
}

function logActivity(string $action, string $entity, ?string $entityId = null, ?string $detail = null): void {
    try {
        $u = currentUser();
        q('INSERT INTO activity_logs (id, user_id, action, entity, entity_id, detail) VALUES (?, ?, ?, ?, ?, ?)',
            [newId(), $u['id'] ?? null, $action, $entity, $entityId, $detail]);
    } catch (Throwable $e) {}
}
