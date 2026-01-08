<?php
// auth.php - session helpers
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.html');
        exit;
    }
}

function login_user($user_id, $identifier)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    // regenerate id
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    // store identifier (email or username)
    $_SESSION['email'] = $identifier;
}

function login_user_with_role($user_id, $identifier, $role)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $identifier;
    $_SESSION['role'] = $role;
}

function logout_user()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
