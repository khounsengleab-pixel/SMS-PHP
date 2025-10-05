<?php
// school-management/pages/index.php

// This page acts as the main entry point for the /pages/ directory.

// Start session to check login status
session_start();

// If a user session exists, redirect to the main application dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    // If no user is logged in, redirect to the login page.
    header('Location: login.php');
    exit;
}
