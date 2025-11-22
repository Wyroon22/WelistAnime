<?php
// functions.php
session_start();

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: signin.php');
        exit;
    }
}

function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
