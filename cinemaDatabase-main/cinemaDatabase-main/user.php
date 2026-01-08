<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/nav.php';
require_login();
$userId = $_SESSION['user_id'];
$highlightTicket = $_GET['ticket'] ?? null;
// fetch user
$stmt = $mysqli->prepare('SELECT id,email,role,balance,created_at FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i',$userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
// fetch tickets
$ts = $mysqli->prepare('SELECT ticket_id, movie_id, price, payload, created_at FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$ts->bind_param('i',$userId);
$ts->execute();
$tres = $ts->get_result();
?>
    <header class="site-header">
      <h1>Account</h1>
      <p class="sub">Manage your account and view transaction history</p>
      <?php if ($highlightTicket): ?>
        <div style="margin-top:8px;padding:8px;background:#e6f7ff;border-radius:8px;border-left:4px solid #0b69ff">
          <strong style="color:#0b3b5a">✓ Ticket Verified</strong>
          <p style="margin:4px 0 0 0;font-size:0.9rem;color:#0b3b5a">Ticket: <?=htmlspecialchars($highlightTicket)?></p>
        </div>
      <?php endif; ?>
    </header>

    <div class="cards">
      <section class="card">
        <h2>Profile</h2>
        <p><strong>Email:</strong> <?=htmlspecialchars($user['email'])?></p>
        <p><strong>Account ID:</strong> <?=htmlspecialchars($user['id'])?></p>
        
        <form method="post" action="top_up.php" style="margin-top:12px;display:flex;gap:8px;align-items:center">
          <input name="amount" type="number" step="0.01" min="1" placeholder="Amount (₱)" style="flex:1;padding:8px;border-radius:8px;border:1px solid #e2e8f0">
          <button class="btn primary" type="submit">Add Balance</button>
        </form>
        <?php if ($user['role'] !== 'admin'): ?>
          <form method="post" action="become_admin.php" style="margin-top:8px">
            <button class="btn primary" type="submit" style="width:100%">Become Admin</button>
          </form>
        <?php else: ?>
          <form method="post" action="make_admin.php" style="margin-top:8px">
            <input type="hidden" name="id" value="<?=htmlspecialchars($user['id'])?>">
            <input type="hidden" name="role" value="user">
            <button class="btn logout" type="submit" style="width:100%">Demote to User</button>
          </form>
        <?php endif; ?>
      </section>

      <section class="card">
        <h2>Recent Tickets</h2>
        <?php if ($tres && $tres->num_rows): ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
          <?php while ($r = $tres->fetch_assoc()): 
            $isHighlighted = ($highlightTicket && $r['ticket_id'] === $highlightTicket);
            $bgColor = $isHighlighted ? '#fffbf0' : '#fff';
            $borderColor = $isHighlighted ? '4px solid #0b69ff' : '1px solid #eee';
            $qrUrl = 'user.php?ticket=' . urlencode($r['ticket_id']);
            $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrUrl);
          ?>
            <div style="padding:12px;border-radius:8px;background:<?=$bgColor?>;border:<?=$borderColor?>;text-align:center">
              <strong style="font-size:0.9rem;display:block;margin-bottom:8px;word-break:break-all"><?=htmlspecialchars($r['ticket_id'])?></strong>
              <img src="<?=$qrSrc?>" alt="QR Code" style="width:160px;height:160px;border-radius:4px;margin-bottom:8px">
              <div style="font-size:0.85rem;color:#666;margin-bottom:4px"><?= $r['movie_id'] ? 'Movie #'.htmlspecialchars($r['movie_id']) : 'Placeholder' ?></div>
              <div style="font-size:0.85rem;color:#666;margin-bottom:4px">₱<?=number_format((float)$r['price'],2)?></div>
              <div style="font-size:0.8rem;color:#999"><?=htmlspecialchars($r['created_at'])?></div>
              <?php if($isHighlighted): ?>
                <div style="margin-top:8px;font-size:1.2rem">⭐</div>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p class="empty">No tickets yet.</p>
        <?php endif; ?>
      </section>
    </div>

    <footer class="site-footer">
      <div class="footer-note">Account management page</div>
    </footer>
    </div>
  </body>
</html>
