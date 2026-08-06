<?php
namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware {
    public static function check() {
        Session::start();
        if (!Session::has('user_id')) {
            header('Location: ' . \App\Config\App::BASE_URL . '/login');
            exit;
        }
        return true;
    }

    public static function guest() {
        Session::start();
        if (Session::has('user_id')) {
            header('Location: ' . \App\Config\App::BASE_URL . '/dashboard');
            exit;
        }
        return true;
    }

    public static function adminOnly() {
        self::check();
        if (Session::get('user_role') !== 'admin') {
            Session::setFlash('error', 'Akses ditolak!');
            header('Location: ' . \App\Config\App::BASE_URL . '/dashboard');
            exit;
        }
        return true;
    }

    public static function adminOrOwner() {
        self::check();
        if (!in_array(Session::get('user_role'), ['admin', 'owner'])) {
            Session::setFlash('error', 'Akses ditolak!');
            header('Location: ' . \App\Config\App::BASE_URL . '/dashboard');
            exit;
        }
        return true;
    }
}