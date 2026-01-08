<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: cinema.php');
    exit;
}
require_once __DIR__ . '/nav.php';

// Purge archived older than 30 days (server-side cleanup)
$mysqli->query("DELETE FROM movies WHERE archived_at IS NOT NULL AND archived_at < (NOW() - INTERVAL 30 DAY)");

$res = $mysqli->query('SELECT id, title, director, year, description, image, price, archived_at FROM movies WHERE archived_at IS NOT NULL ORDER BY archived_at DESC');
?>
    <header class="site-header">
      <h1>Archived Movies</h1>
      <p class="sub">Movies that were archived (older than 30 days are purged automatically)</p>
    </header>

    <section class="card">
      <h2>Archived</h2>
      <?php if ($res && $res->num_rows): ?>
        <ul style="list-style:none;padding:0;margin:0">
        <?php while ($r = $res->fetch_assoc()): ?>
          <li style="padding:12px;border-top:1px solid #eee;display:flex;justify-content:space-between;align-items:center">
            <div>
              <strong><?=htmlspecialchars($r['title'])?></strong>
              <div class="meta"><?=htmlspecialchars($r['director'])?> <?= $r['year'] ? '• ' . htmlspecialchars($r['year']) : '' ?></div>
              <div class="meta">Archived at: <?=htmlspecialchars($r['archived_at'])?></div>
            </div>
            <div>
              <form method="post" action="restore.php" style="display:inline-block;margin:0">
                <input type="hidden" name="id" value="<?=htmlspecialchars($r['id'])?>">
                <button class="btn primary" type="submit">Restore</button>
              </form>
              <form method="post" action="delete_movie.php" style="display:inline-block;margin:0 0 0 8px">
                <input type="hidden" name="id" value="<?=htmlspecialchars($r['id'])?>">
                <button class="btn logout" type="submit">Delete</button>
              </form>
            </div>
          </li>
        <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No archived movies.</p>
      <?php endif; ?>
    </section>

    <footer class="site-footer">
      <div class="footer-note">Archive management</div>
    </footer>
    </div>
  </body>
</html>
