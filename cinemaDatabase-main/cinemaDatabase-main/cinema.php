<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/nav.php';
?>
    <header class="site-header">
      <h1>Now Showing</h1>
      <p class="sub">Browse movies — click a card to view details and buy tickets</p>
    </header>

    <section class="card list-card movies-top">
      <h2>Movies</h2>
      <div id="moviesRow" class="movies-row"></div>
    </section>

    <footer class="site-footer">
      <div class="footer-note">Cinema Database</div>
    </footer>
    </div>
  </body>
</html>

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

  <script>
    window.IS_ADMIN = <?php echo (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'true' : 'false'; ?>;
    window.USER_EMAIL = <?php echo json_encode($_SESSION['email'] ?? ''); ?>;
    window.USER_BALANCE = <?php echo json_encode($_SESSION['balance'] ?? null); ?>;
  </script>
  <script src="script.js"></script>
</body>
</html>
