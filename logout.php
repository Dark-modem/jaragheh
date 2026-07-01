<?php
require_once __DIR__ . '/backend/core/helpers.php';
logout_user();
redirect('login.php');
