<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: cinema.php');
    exit;
}
require_once __DIR__ . '/nav.php';
// fetch users
$res = $mysqli->query('SELECT id, email, role, created_at FROM users ORDER BY id ASC');
?>
    <header class="site-header">
      <h1>Admin Settings</h1>
      <p class="sub">Promote or demote accounts</p>
    </header>

    <section class="card">
      <h2>User Accounts</h2>
      <table style="width:100%;border-collapse:collapse">
        <thead><tr><th style="text-align:left;padding:8px">ID</th><th style="text-align:left;padding:8px">Email</th><th style="text-align:left;padding:8px">Role</th><th style="text-align:left;padding:8px">Since</th><th style="text-align:left;padding:8px">Actions</th></tr></thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td style="padding:8px;border-top:1px solid #eee"><?=htmlspecialchars($row['id'])?></td>
            <td style="padding:8px;border-top:1px solid #eee"><?=htmlspecialchars($row['email'])?></td>
            <td style="padding:8px;border-top:1px solid #eee"><?=htmlspecialchars($row['role'])?></td>
            <td style="padding:8px;border-top:1px solid #eee"><?=htmlspecialchars($row['created_at'])?></td>
            <td style="padding:8px;border-top:1px solid #eee">
              <?php if ($row['role'] !== 'admin'): ?>
                <form method="post" action="make_admin.php" style="display:inline-block;margin:0">
                  <input type="hidden" name="id" value="<?=htmlspecialchars($row['id'])?>">
                  <input type="hidden" name="role" value="admin">
                  <button class="btn primary" type="submit">Make Admin</button>
                </form>
              <?php else: ?>
                <form method="post" action="make_admin.php" style="display:inline-block;margin:0">
                  <input type="hidden" name="id" value="<?=htmlspecialchars($row['id'])?>">
                  <input type="hidden" name="role" value="user">
                  <button class="btn" type="submit">Demote</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <footer class="site-footer">
      <div class="footer-note">Manage user roles and permissions</div>
    </footer>
    </div>
  </body>
</html>
