<?php
// functions.php
session_start();

function is_login() {
    return !empty($_SESSION["user"]);
}

function current_user() {
    return $_SESSION["user"] ?? null;
}

function require_login() {
    if (!is_login()) {
        header("Location: signin.php");
        exit;
    }
}

function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
