<?php
/*
 * Page protection helper.
 *
 * Put this ONE line at the very top of any page that should be private,
 * right after `include 'includes/db.php';`:
 *
 *     include 'includes/auth.php';
 *     require_login();            // any logged-in user (admin or cashier)
 *     require_login('admin');     // admins only
 *
 * If the visitor isn't allowed, they get bounced back to the login page
 * BEFORE any of the page's content is sent. (db.php already started the
 * session, so $_SESSION is available here.)
 */

function require_login($required_role = null)
{
    // 1. Must be logged in at all.
    if (empty($_SESSION['logged_in'])) {
        header("Location: index.php");
        exit();
    }

    // 2. If a specific role is required, enforce it.
    if ($required_role !== null && ($_SESSION['role'] ?? '') !== $required_role) {
        header("Location: index.php?error=access_denied");
        exit();
    }
}
