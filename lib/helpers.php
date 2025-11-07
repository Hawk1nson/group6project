<?php

/**
 * Sanitize input data
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for HTML
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    $path = trim($path);

    // Full URLs: leave alone
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header('Location: ' . $path);
        exit;
    }

    // Paths that already include BASE_URL: leave alone
    if (defined('BASE_URL') && $path !== '' && strpos($path, rtrim(BASE_URL, '/') . '/') === 0) {
        header('Location: ' . $path);
        exit;
    }

    // Absolute-from-root (starts with "/"): rewrite to BASE_URL + trimmed
    if (isset($path[0]) && $path[0] === '/') {
        $path = ltrim($path, '/'); // "public/dashboard.php"
    }

    // Build final target with BASE_URL
    $target = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    header('Location: ' . $target);
    exit;
}

/**
 * Display a flash message
 */
function flash($name = '', $message = '', $class = 'alert alert-success')
{
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}


/**
 * Build a safe image src from stored vehicle image values.
 * Accepts bare filenames, paths starting with /, or full URLs.
 * Returns a document-root relative path starting with '/' or a remote URL.
 */
function vehicle_img_src($val): string
{
    $val = trim((string)$val);
    if ($val === '') return '';

    // full remote URL
    if (preg_match('~^(https?:)?//~i', $val)) {
        return preg_replace('/\s+/', '%20', $val);
    }

    // already absolute-from-root
    if (isset($val[0]) && $val[0] === '/') {
        $path = ltrim($val, '/');
        $segments = array_map('rawurlencode', explode('/', $path));
        return '/' . implode('/', $segments);
    }

    $path = ltrim($val, '/');

    if (str_starts_with($path, 'cdms/')) {
        // leave as-is
    } elseif (str_starts_with($path, 'images/')) {
        $path = 'cdms/' . $path;
    } else {
        // treat as filename stored in images/vehicles
        $path = 'cdms/images/vehicles/' . $path;
    }

    $segments = array_map('rawurlencode', explode('/', $path));
    return '/' . implode('/', $segments);
}
