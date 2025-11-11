<?php

class LogoutController {
    public function handle() {
        session_unset();
        session_destroy();
        header('Location: inicio.php');
        exit();
    }
}
