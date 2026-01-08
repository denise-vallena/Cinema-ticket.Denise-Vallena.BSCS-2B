<?php
// nav.php - Shared navigation sidebar
// Include this file at the start of each page after auth checks
// Then wrap the page content in a <div class="main-content">...</div>

// Determine the current page for styling active links
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$userBalance = isset($_SESSION['balance']) ? $_SESSION['balance'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="with-nav">
    <nav class="sidebar">
        <div class="brand">
            <h2>Cinema</h2>
        </div>
        
        <ul class="nav-links">
            <li>
                <a href="cinema.php" class="<?php echo ($currentPage === 'cinema.php') ? 'active' : ''; ?>">
                    🏠 Home
                </a>
            </li>
            <li>
                <a href="user.php" class="<?php echo ($currentPage === 'user.php') ? 'active' : ''; ?>">
                    👤 Account
                </a>
            </li>
            <?php if ($isAdmin): ?>
            <li>
                <a href="index.php" class="<?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
                    ⚙️ Admin
                </a>
            </li>
            <li>
                <a href="admin_settings.php" class="<?php echo ($currentPage === 'admin_settings.php') ? 'active' : ''; ?>">
                    👥 Users
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <?php if (! $isAdmin): ?>
        <div class="admin-button-section" style="display:none">
            <form method="post" action="become_admin.php" style="margin:0">
                <button class="btn-admin" type="submit">⭐ Become Admin</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="nav-footer">
            <div class="balance-display">
                Balance: <strong id="userBalance">₱<?php echo number_format($userBalance, 2); ?></strong>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="main-content">
