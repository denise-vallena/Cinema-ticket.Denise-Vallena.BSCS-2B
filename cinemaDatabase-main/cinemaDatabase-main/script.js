document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('movieForm');
  const msg = document.getElementById('msg');
  const list = document.getElementById('movies');

  function showMessage(text, ok = true) {
    msg.textContent = text;
    msg.className = ok ? 'ok' : 'err';
    setTimeout(() => msg.textContent = '', 3000);
  }

  const moviesRow = document.getElementById('moviesRow');
  const modal = document.getElementById('movieModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalMeta = document.getElementById('modalMeta');
  const modalDescription = document.getElementById('modalDescription');
  const modalClose = document.getElementById('modalClose');
  const buyBtn = document.getElementById('buyBtn');

  async function loadMovies() {
    const scope = (typeof window.IS_ADMIN !== 'undefined' && window.IS_ADMIN) ? 'all' : 'public';
    const res = await fetch('fetch.php?scope=' + encodeURIComponent(scope));
    const data = await res.json();
    moviesRow.innerHTML = '';
    
    if (!Array.isArray(data) || data.length === 0) {
      moviesRow.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#888;padding:40px">No movies available yet.</p>';
      return;
    }
    data.forEach(m => {
      const card = document.createElement('div');
      card.className = 'movie-card';
      card.dataset.id = m.id;
      card.dataset.title = m.title;
      card.dataset.director = m.director || '';
      card.dataset.year = m.year || '';
      card.dataset.description = m.description || '';
      card.dataset.image = m.image || '';
      card.dataset.price = m.price || '';
      card.dataset.published = m.published ? 1 : 0;
      card.innerHTML = `
        <div class="thumb"><img src="${m.image ? escapeHtml(m.image) : 'data:image/svg+xml;utf8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22600%22%3E%3Crect width=%22100%25%22 height=%22100%25%22 fill=%22%236f3bff%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2232%22 fill=%22%23fff%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22%3EMovie Poster%3C/text%3E%3C/svg%3E'}" alt="${escapeHtml(m.title)}" style="width:100%;height:100%;object-fit:cover"></div>
        <h4>${escapeHtml(m.title)}</h4>
        <div class="meta">${escapeHtml(m.director || '')} ${m.year ? '• ' + m.year : ''} ${m.price ? '• ₱' + parseFloat(m.price).toFixed(2) : ''}</div>`;
      // admin controls
      if (window.IS_ADMIN) {
        const adminBar = document.createElement('div');
        adminBar.style.marginTop = '8px';
        
        const pubBtn = document.createElement('button');
        pubBtn.className = 'btn';
        pubBtn.textContent = (m.published ? 'Unpublish' : 'Publish');
        pubBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const form = new FormData();
          form.append('id', m.id);
          form.append('published', m.published ? 0 : 1);
          const res = await fetch('publish.php', { method: 'POST', body: form });
          const result = await res.json();
          if (result.ok) {
            pubBtn.textContent = (result.published ? 'Unpublish' : 'Publish');
            m.published = result.published;
          } else alert('Publish failed');
        });
        adminBar.appendChild(pubBtn);

        const archBtn = document.createElement('button');
        archBtn.className = 'btn';
        archBtn.style.marginLeft = '8px';
        archBtn.textContent = 'Archive';
        archBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!confirm('Archive this movie?')) return;
          const form = new FormData(); form.append('id', m.id);
          const res = await fetch('archive.php', { method: 'POST', body: form });
          const result = await res.json();
          if (result.ok) { card.remove(); } else { alert('Archive failed: ' + (result.message || result.error)); }
        });
        adminBar.appendChild(archBtn);

        const delBtn = document.createElement('button');
        delBtn.className = 'btn logout';
        delBtn.style.marginLeft = '8px';
        delBtn.textContent = 'Delete';
        delBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!confirm('Delete this movie?')) return;
          const form = new FormData(); form.append('id', m.id);
          const res = await fetch('delete_movie.php', { method: 'POST', body: form });
          const result = await res.json();
          if (result.ok) { card.remove(); } else { alert('Delete failed: ' + (result.message || result.error)); }
        });
        adminBar.addEventListener('click', (e)=> e.stopPropagation());
        adminBar.appendChild(delBtn);
        card.appendChild(adminBar);
      }
      card.addEventListener('click', () => openModal(card.dataset));
      moviesRow.appendChild(card);
    });
  }

  function openModal(data) {
    modalTitle.textContent = data.title || '';
    modalMeta.textContent = (data.director ? data.director + (data.year ? ' • ' + data.year : '') : (data.year ? data.year : ''));
    modalDescription.textContent = data.description || 'No description provided.';
    buyBtn.onclick = async () => {
      const name = (window.USER_EMAIL && window.USER_EMAIL !== '') ? window.USER_EMAIL : prompt('Enter your name for the ticket receipt') || 'Guest';
      const confirmed = confirm('Confirm purchase of "' + (data.title || '') + '" for $' + (data.price ? parseFloat(data.price).toFixed(2) : '0.00') + '?');
      if (!confirmed) return;
      // call server to create ticket and deduct balance
      try {
        const fd = new FormData();
        fd.append('id', data.id);
        fd.append('buyer', name);
        const res = await fetch('create_ticket.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (!json.ok) {
          alert('Purchase failed: ' + (json.error || json.message || 'unknown'));
          return;
        }
        const ticketId = json.ticketId;
        const payload = json.payload;
        const price = (typeof json.price !== 'undefined') ? parseFloat(json.price).toFixed(2) : (data.price ? parseFloat(data.price).toFixed(2) : '0.00');
        // update client-side displayed balance if provided
        if (typeof json.balance !== 'undefined') {
          window.USER_BALANCE = json.balance;
          const balEl = document.getElementById('userBalance');
          if (balEl) balEl.textContent = '₱' + parseFloat(json.balance).toFixed(2);
        }
        // render receipt in modal
        const modalContent = modal.querySelector('.modal-content');
        const ts = (payload && payload.issued) ? payload.issued : new Date().toLocaleString();
        const receiptHtml = `
          <button id="modalClose" class="modal-close">×</button>
          <h3>Receipt — ${escapeHtml(data.title || '')}</h3>
          <p class="meta">${escapeHtml(data.director || '')} ${data.year ? '• ' + escapeHtml(data.year) : ''}</p>
          <p>${escapeHtml(data.description || '')}</p>
          <hr>
          <p><strong>Buyer:</strong> ${escapeHtml(name)}</p>
          <p><strong>Price:</strong> ₱${escapeHtml(price)}</p>
          <p><strong>Ticket ID:</strong> ${escapeHtml(ticketId)}</p>
          <p><strong>Issued:</strong> ${escapeHtml(ts)}</p>
          <div style="margin-top:12px;text-align:center">
            <img id="ticketQr" alt="Ticket QR Code" style="width:220px;height:220px;border:2px solid #ddd;padding:8px;border-radius:8px;background:#fff">
          </div>
          <div style="margin-top:12px;text-align:center;font-size:0.9rem;color:#666">
            <p>Scan this QR code to view your ticket history</p>
          </div>
          <div style="margin-top:12px;text-align:center">
            <button id="downloadTicket" class="btn primary">Download Ticket</button>
          </div>
        `;
        modalContent.innerHTML = receiptHtml;
        modal.querySelector('#modalClose').addEventListener('click', () => modal.setAttribute('aria-hidden', 'true'));
        
        // Encode a scannable URL that points to user history
        const currentUrl = window.location.origin + window.location.pathname;
        const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/') + 1);
        const qrUrl = baseUrl + 'user.php?ticket=' + encodeURIComponent(ticketId);
        const qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(qrUrl);
        const qrImg = modal.querySelector('#ticketQr');
        qrImg.src = qrSrc;
        qrImg.style.display = 'block';
        qrImg.onerror = function() {
          this.style.display = 'none';
          const fallback = document.createElement('p');
          fallback.textContent = 'QR Code: ' + qrUrl;
          fallback.style.fontSize = '0.85rem';
          fallback.style.wordBreak = 'break-all';
          this.parentNode.insertBefore(fallback, this.nextSibling);
        };
        
        modal.querySelector('#downloadTicket').addEventListener('click', () => {
          const a = document.createElement('a');
          a.href = qrSrc;
          a.download = ticketId + '.png';
          document.body.appendChild(a);
          a.click();
          a.remove();
        });
      } catch (e) {
        alert('Purchase failed: ' + e.message);
      }
    };
    modal.setAttribute('aria-hidden', 'false');
  }

  modalClose.addEventListener('click', () => modal.setAttribute('aria-hidden', 'true'));
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.setAttribute('aria-hidden', 'true'); });

  function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      // send as FormData to support file upload
      const fd = new FormData(form);
      const fileInput = document.querySelector('input[name="image"]');
      if (fileInput && fileInput.files && fileInput.files[0]) fd.set('image', fileInput.files[0]);
      const priceInput = document.querySelector('input[name="price"]');
      if (priceInput) fd.set('price', priceInput.value);
      // if admin is adding, publish immediately
      if (typeof window.IS_ADMIN !== 'undefined' && window.IS_ADMIN) fd.set('published', '1');
      const res = await fetch('add.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.ok) {
        showMessage('Movie added');
        form.reset();
        loadMovies();
      } else {
        showMessage('Error: ' + (data.error || 'unknown'), false);
      }
    });
  }

  loadMovies();
  // reveal admin link if present and user is admin
  try {
    if (typeof window.IS_ADMIN !== 'undefined' && window.IS_ADMIN) {
      const adminLink = document.getElementById('adminLink');
      if (adminLink) adminLink.style.display = 'inline-block';
    }
  } catch (e) {}
});
