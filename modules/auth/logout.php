<?php
require_once __DIR__ . '/../../config/db.php';

session_unset();
session_destroy();

header('Location: /QuanLyDanhBaCaNhan/index.php?auth=login');
exit;