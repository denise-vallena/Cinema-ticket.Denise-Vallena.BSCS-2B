<?php
require_once __DIR__ . '/auth.php';
require_login();
// ensure admin only
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: cinema.php');
  exit;
}
require_once __DIR__ . '/nav.php';
?>
    <header class="site-header">
      <h1>Cinema Database</h1>
      <p class="sub">Welcome, <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
      <div style="margin-top:8px;display:flex;gap:8px;align-items:center">
        <a href="admin_settings.php" class="btn" style="background:#222;color:#fff;padding:8px 10px;border-radius:8px;text-decoration:none">Admin Settings</a>
        <a href="admin_archive.php" class="btn" style="background:#6b2a88;color:#fff;padding:8px 10px;border-radius:8px;text-decoration:none">Archive</a>
        <a href="cinema.php" class="btn" style="background:#1b6f1b;color:#fff;padding:8px 10px;border-radius:8px;text-decoration:none">View Site</a>
      </div>
    </header>

    <div class="cards">
      <section class="card list-card movies-top">
        <h2>Movies</h2>
        <div id="moviesRow" class="movies-row"></div>
      </section>

      <section class="card form-card">
        <h2>Add Movie</h2>
        <form id="movieForm" enctype="multipart/form-data">
          <label>Title <input name="title" required></label>
          <label>Director <input name="director"></label>
          <label>Year <input name="year" type="number" min="1800" max="2100"></label>
          <label>Price (USD) <input name="price" type="number" step="0.01" min="0"></label>
          <label>Description <textarea name="description" rows="3"></textarea></label>
          <label>Poster Image <input name="image" type="file" accept="image/*"></label>
          <div class="actions">
            <button type="submit" class="btn primary">Add Movie</button>
          </div>
        </form>
        <div id="msg"></div>
      </section>
    </div>

    <footer class="site-footer">
      <div class="footer-note">Created by Denise Vallena</div>
    </footer>
    </div>

  <script>
    window.IS_ADMIN = <?php echo (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'true' : 'false'; ?>;
    window.USER_EMAIL = <?php echo json_encode($_SESSION['email'] ?? ''); ?>;
    window.USER_BALANCE = <?php echo json_encode($_SESSION['balance'] ?? null); ?>;
  </script>
  <script src="script.js"></script>

  <div id="movieModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <button id="modalClose" class="modal-close">×</button>
      <h3 id="modalTitle"></h3>
      <p id="modalMeta" class="meta"></p>
      <p id="modalDescription"></p>
      <div class="modal-actions">
        <button id="buyBtn" class="btn primary">Buy Ticket</button>
      </div>
    </div>
  </div>
</body>
</html>
