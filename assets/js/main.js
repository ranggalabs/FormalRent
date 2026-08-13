document.addEventListener('DOMContentLoaded', () => {
  // Chatbot Modal Toggle & Simulation Engine
  const chatbotModal = document.getElementById('chatbotModal');
  const chatMessages = document.getElementById('chatMessages');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');

  // Robust Global Click Handler for Chatbot Triggers
  document.addEventListener('click', (e) => {
    // Open Chatbot
    if (e.target.closest('#chatbotToggleBtn') || e.target.closest('#chatbotBubble') || e.target.closest('.chatbot-btn') || e.target.closest('.chatbot-bubble')) {
      if (chatbotModal) {
        chatbotModal.classList.add('open');
      }
    }
    // Close Chatbot
    if (e.target.closest('#chatbotCloseBtn') || e.target.closest('.chatbot-close-btn')) {
      if (chatbotModal) {
        chatbotModal.classList.remove('open');
      }
    }
  });

  if (chatbotModal && chatMessages) {
    // Mini Database for Chatbot Responses
    const botData = {
      low: [
        { title: 'Kemeja Formal', price: 'Rp 75.000', img: 'assets/cat_jas.png' },
        { title: 'Set Toga Wisuda', price: 'Rp 100.000', img: 'assets/cat_toga.png' },
        { title: 'Oxford Leather Shoes', price: 'Rp 150.000', img: 'assets/product_shoes.png' }
      ],
      mid: [
        { title: 'Maroon Slim Fit', price: 'Rp 300.000', img: 'assets/product_maroon.png' },
        { title: 'Classic Navy Tuxedo', price: 'Rp 350.000', img: 'assets/product_tuxedo.png' },
        { title: 'Modern Ivory Kebaya', price: 'Rp 450.000', img: 'assets/product_kebaya.png' }
      ],
      high: [
        { title: 'Sogan Keraton Silk', price: 'Rp 850.000', img: 'assets/catalog_batik3.png' },
        { title: 'Navy Royale Slim Fit', price: 'Rp 1.250.000', img: 'assets/catalog_suit2.png' },
        { title: 'Midnight Velvet Tuxedo', price: 'Rp 1.500.000', img: 'assets/catalog_tux1.png' }
      ]
    };

    const appendUserMessage = (text) => {
      const msgDiv = document.createElement('div');
      msgDiv.className = 'chat-msg user-msg';
      msgDiv.innerHTML = `<div class="msg-bubble">${text}</div>`;
      chatMessages.appendChild(msgDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    const appendBotReply = (text, items = []) => {
      const msgDiv = document.createElement('div');
      msgDiv.className = 'chat-msg bot-msg';
      
      let itemsHTML = '';
      if (items.length > 0) {
        itemsHTML = items.map(item => `
          <div class="chat-product-recommendation">
            <img src="${item.img}" alt="${item.title}">
            <div class="chat-product-details">
              <span class="chat-product-title">${item.title}</span>
              <span class="chat-product-price">${item.price} / 3 hari</span>
              <a href="katalog.php" class="chat-product-link">Lihat di Katalog →</a>
            </div>
          </div>
        `).join('');
      }

      msgDiv.innerHTML = `<div class="msg-bubble">${text} ${itemsHTML}</div>`;
      chatMessages.appendChild(msgDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    const processAction = (action, labelText) => {
      appendUserMessage(labelText);

      setTimeout(() => {
        if (action === 'budget_low') {
          appendBotReply('Berikut rekomendasi busana hemat di bawah **Rp 200rb**:', botData.low);
        } else if (action === 'budget_mid') {
          appendBotReply('Berikut pilihan terlaris untuk budget **Rp 200k - 500k**:', botData.mid);
        } else if (action === 'budget_high') {
          appendBotReply('Berikut koleksi busana formal premium kualitas butik terbaik kami:', botData.high);
        } else if (action === 'find_jas') {
          appendBotReply('Berikut koleksi Jas & Tuxedo pria terpopuler:', [botData.mid[0], botData.mid[1], botData.high[2]]);
        } else if (action === 'find_kebaya') {
          appendBotReply('Berikut koleksi Kebaya Modern favorit pelanggan:', [botData.mid[2], botData.high[0]]);
        }
      }, 300);
    };

    // Handle suggestion chips click
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('suggestion-chip')) {
        const action = e.target.dataset.action;
        const text = e.target.textContent;
        processAction(action, text);
      }
    });

    // Handle form input
    if (chatForm && chatInput) {
      chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const val = chatInput.value.trim();
        if (!val) return;

        appendUserMessage(val);
        chatInput.value = '';

        const lower = val.toLowerCase();
        setTimeout(() => {
          if (lower.includes('100') || lower.includes('150') || lower.includes('75') || lower.includes('hemat') || lower.includes('murah')) {
            appendBotReply(`Berdasarkan budget Anda (~${val}), berikut rekomendasi terbaik kami:`, botData.low);
          } else if (lower.includes('200') || lower.includes('300') || lower.includes('400') || lower.includes('500') || lower.includes('standar')) {
            appendBotReply(`Berdasarkan pencarian Anda (~${val}), berikut koleksi favorit kami:`, botData.mid);
          } else if (lower.includes('800') || lower.includes('juta') || lower.includes('1.5') || lower.includes('premium') || lower.includes('mewah')) {
            appendBotReply(`Berikut koleksi mewah eksklusif yang sesuai dengan kriteria Anda:`, botData.high);
          } else if (lower.includes('jas') || lower.includes('tuxedo')) {
            appendBotReply('Berikut rekomendasi Jas & Tuxedo formal pria:', [botData.mid[0], botData.mid[1], botData.high[2]]);
          } else if (lower.includes('kebaya')) {
            appendBotReply('Berikut rekomendasi Kebaya cantik untuk acara Anda:', [botData.mid[2], botData.high[0]]);
          } else {
            appendBotReply(`Terima kasih! Saya telah mencari di katalog untuk "${val}". Silakan lihat rekomendasi ini atau cek katalog lengkap:`, botData.mid);
          }
        }, 300);
      });
    }
  }



  // Password Visibility Toggle
  const togglePasswordBtn = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');

  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      const svg = togglePasswordBtn.querySelector('svg');
      if (type === 'text') {
        svg.innerHTML = `
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        `;
      } else {
        svg.innerHTML = `
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" fill="none"/>
          <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
        `;
      }
    });
  }

  // Password Strength Meter
  const bar1 = document.getElementById('bar1');
  const bar2 = document.getElementById('bar2');
  const bar3 = document.getElementById('bar3');
  const strengthText = document.getElementById('strengthText');

  if (passwordInput && bar1 && bar2 && bar3 && strengthText) {
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      let score = 0;

      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val) && val.length >= 10) score++;

      bar1.style.backgroundColor = '#E5E1DA';
      bar2.style.backgroundColor = '#E5E1DA';
      bar3.style.backgroundColor = '#E5E1DA';

      if (val.length === 0) {
        strengthText.textContent = 'Kekuatan password: Lemah';
        strengthText.style.color = '#564242';
      } else if (score <= 1) {
        bar1.style.backgroundColor = '#B3261E';
        strengthText.textContent = 'Kekuatan password: Lemah';
        strengthText.style.color = '#B3261E';
      } else if (score === 2) {
        bar1.style.backgroundColor = '#D97706';
        bar2.style.backgroundColor = '#D97706';
        strengthText.textContent = 'Kekuatan password: Sedang';
        strengthText.style.color = '#D97706';
      } else {
        bar1.style.backgroundColor = '#059669';
        bar2.style.backgroundColor = '#059669';
        bar3.style.backgroundColor = '#059669';
        strengthText.textContent = 'Kekuatan password: Kuat';
        strengthText.style.color = '#059669';
      }
    });
  }

  // Catalog Interactive Filter System
  const searchInput = document.getElementById('catalogSearchInput');
  const priceRange = document.getElementById('priceRange');
  const priceMaxLabel = document.getElementById('priceMaxLabel');
  const resetBtn = document.getElementById('resetFiltersBtn');
  const catalogGrid = document.getElementById('catalogGrid');
  const sizeChips = document.querySelectorAll('.btn-size-chip');

  if (catalogGrid) {
    const items = catalogGrid.querySelectorAll('.catalog-item-card');

    const filterCatalog = () => {
      const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
      const maxPrice = priceRange ? parseInt(priceRange.value, 10) : 2500000;
      
      const activeStatusRadio = document.querySelector('input[name="statusFilter"]:checked');
      const selectedStatus = activeStatusRadio ? activeStatusRadio.value : 'ALL';

      const checkedCategories = Array.from(document.querySelectorAll('.cat-filter:checked')).map(cb => cb.value);
      const selectedSizes = Array.from(document.querySelectorAll('.btn-size-chip.active')).map(btn => btn.dataset.size);

      items.forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const code = card.dataset.code.toLowerCase();
        const cat = card.dataset.cat;
        const size = card.dataset.size;
        const price = parseInt(card.dataset.price, 10);
        const status = card.dataset.status;

        const matchesQuery = query === '' || name.includes(query) || code.includes(query);
        const matchesCat = checkedCategories.length === 0 || checkedCategories.includes(cat);
        const matchesSize = selectedSizes.length === 0 || selectedSizes.includes(size);
        const matchesPrice = price <= maxPrice;
        const matchesStatus = selectedStatus === 'ALL' || status === selectedStatus;

        if (matchesQuery && matchesCat && matchesSize && matchesPrice && matchesStatus) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    };

    if (searchInput) searchInput.addEventListener('input', filterCatalog);
    
    if (priceRange) {
      priceRange.addEventListener('input', () => {
        const val = parseInt(priceRange.value, 10);
        if (priceMaxLabel) priceMaxLabel.textContent = `Maks: Rp ${(val / 1000000).toFixed(1)}jt`;
        filterCatalog();
      });
    }

    document.querySelectorAll('input[name="statusFilter"]').forEach(radio => {
      radio.addEventListener('change', filterCatalog);
    });

    document.querySelectorAll('.cat-filter').forEach(cb => {
      cb.addEventListener('change', filterCatalog);
    });

    sizeChips.forEach(chip => {
      chip.addEventListener('click', () => {
        chip.classList.toggle('active');
        filterCatalog();
      });
    });

    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (priceRange) {
          priceRange.value = 2500000;
          if (priceMaxLabel) priceMaxLabel.textContent = 'Maks: Rp 2.5jt';
        }
        document.querySelectorAll('.cat-filter').forEach(cb => cb.checked = false);
        const defaultTux = document.querySelector('.cat-filter[value="Tuxedo Klasik"]');
        if (defaultTux) defaultTux.checked = true;

        sizeChips.forEach(chip => {
          if (chip.dataset.size === 'M' || chip.dataset.size === 'L') {
            chip.classList.add('active');
          } else {
            chip.classList.remove('active');
          }
        });

        const allStatusRadio = document.querySelector('input[name="statusFilter"][value="ALL"]');
        if (allStatusRadio) allStatusRadio.checked = true;

        filterCatalog();
      });
    }
  }
});
