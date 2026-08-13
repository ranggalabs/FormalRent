<!-- Floating Chatbot Trigger Widget -->
<div class="floating-chatbot">
  <div class="chatbot-bubble" id="chatbotBubble">
    Bingung sesuain budget?<br>Chat aja 👗
  </div>
  <button type="button" class="chatbot-btn" id="chatbotToggleBtn" aria-label="Open Chat">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
    </svg>
  </button>
</div>

<!-- Floating Chatbot Simulation Window -->
<div id="chatbotModal" class="chatbot-modal-wrap">
  <div class="chatbot-modal-card">
    
    <!-- Chatbot Header -->
    <div class="chatbot-modal-header">
      <div class="chatbot-profile">
        <div class="chatbot-avatar">👗</div>
        <div class="chatbot-info">
          <h4 class="chatbot-name">Elegance AI Stylist</h4>
          <span class="chatbot-status">● Online • Asisten Budget & Katalog</span>
        </div>
      </div>
      <button type="button" id="chatbotCloseBtn" class="chatbot-close-btn">&times;</button>
    </div>

    <!-- Chat Messages Container -->
    <div class="chatbot-messages" id="chatMessages">
      <div class="chat-msg bot-msg">
        <div class="msg-bubble">
          Halo! 👋 Saya <strong>Elegance AI Stylist</strong>.<br>
          Bingung memilih busana formal atau ingin menyesuaikan dengan anggaran acara Anda? Silakan pilih opsi di bawah atau ketik budget Anda!
        </div>
      </div>
      
      <!-- Quick Suggestion Buttons -->
      <div class="chat-suggestions" id="chatSuggestions">
        <button type="button" class="suggestion-chip" data-action="budget_low">💡 Budget &lt; Rp 200rb</button>
        <button type="button" class="suggestion-chip" data-action="budget_mid">✨ Budget Rp 200k - 500k</button>
        <button type="button" class="suggestion-chip" data-action="budget_high">👑 Premium (&gt; Rp 500k)</button>
        <button type="button" class="suggestion-chip" data-action="find_jas">👔 Cari Jas / Tuxedo</button>
        <button type="button" class="suggestion-chip" data-action="find_kebaya">👗 Cari Kebaya</button>
      </div>
    </div>

    <!-- Chat Input Form -->
    <form id="chatForm" class="chatbot-input-area">
      <input type="text" id="chatInput" placeholder="Ketik budget / pertanyaan Anda (e.g. 300rb)..." autocomplete="off" required>
      <button type="submit" class="btn-chat-send" aria-label="Kirim Pesan">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </form>

  </div>
</div>
