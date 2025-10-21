<?php
require_once __DIR__ . '/db.php';
session_start();

function auth_login(string $email, string $password): bool
{
    $pdo = DB::conn();
    $st = $pdo->prepare('SELECT employee_id, first_name, last_name, email, role, is_active, password_hash FROM employees WHERE email=? LIMIT 1');
    $st->execute([$email]);
    $u = $st->fetch();
    if (!$u || (int)$u['is_active'] !== 1) return false;

    $stored = (string)($u['password_hash'] ?? '');

    // If the stored value looks like a hash, verify as hash; otherwise compare plaintext.
    $looksHashed = str_starts_with($stored, '$2y$')    // bcrypt
        || str_starts_with($stored, '$argon')  // argon2
        || str_starts_with($stored, '$pbkdf'); // other formats (just in case)

    $ok = $looksHashed ? password_verify($password, $stored)
        : hash_equals($stored, $password);

    if (!$ok) return false;

    $_SESSION['user'] = [
        'id'    => (int)$u['employee_id'],
        'name'  => $u['first_name'] . ' ' . $u['last_name'],
        'role'  => $u['role'],
        'email' => $u['email'],
    ];
    return true;
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}
function auth_check(): bool
{
    return isset($_SESSION['user']);
}
function auth_logout(): void
{
    $_SESSION = [];
    session_destroy();
}
