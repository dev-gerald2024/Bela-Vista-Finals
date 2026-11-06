<?php
/**
 * Bella Vista Oasis Spa - Logout Script
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

flash_message('info', 'You have been safely signed out. We await your return.');
logout('login.php');
