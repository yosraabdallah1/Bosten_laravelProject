@extends('layouts.app')
@section('title', 'Basma — Assistante Bosten')

@section('styles')
<style>
    /* ── Layout ── */
    .chat-wrapper {
        max-width: 740px;
        margin: 2rem auto;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 140px);
        min-height: 520px;
    }

    /* ── Header ── */
    .chat-header {
        background: #2d6a4f;
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: .85rem;
        color: #fff;
    }
    .chat-header .avatar {
        width: 44px; height: 44px;
        background: #52b788;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
        position: relative;
    }
    .chat-header .avatar::after {
        content: '';
        width: 11px; height: 11px;
        background: #40c057;
        border: 2px solid #2d6a4f;
        border-radius: 50%;
        position: absolute;
        bottom: 1px; right: 1px;
    }
    .chat-header .info h6 { margin: 0; font-weight: 700; font-size: 1rem; }
    .chat-header .info small { opacity: .75; font-size: .78rem; }

    /* ── Messages area ── */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
        background: #f8faf8;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        scroll-behavior: smooth;
    }
    .chat-messages::-webkit-scrollbar { width: 5px; }
    .chat-messages::-webkit-scrollbar-thumb { background: #c3d9cd; border-radius: 10px; }

    /* ── Bulles ── */
    .msg-row {
        display: flex;
        margin-bottom: .85rem;
        animation: fadeUp .25s ease;
    }
    .msg-row.user  { justify-content: flex-end; }
    .msg-row.bot   { justify-content: flex-start; }

    .bubble {
        max-width: 72%;
        padding: .65rem 1rem;
        border-radius: 18px;
        font-size: .9rem;
        line-height: 1.5;
        word-break: break-word;
    }
    .msg-row.user .bubble {
        background: #2d6a4f;
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-row.bot .bubble {
        background: #fff;
        color: #212529;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }

    /* Avatar bot dans les bulles */
    .bot-avatar {
        width: 30px; height: 30px;
        background: #d8f3dc;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
        margin-right: .5rem;
        align-self: flex-end;
    }

    .msg-time {
        font-size: .7rem;
        color: #adb5bd;
        margin-top: .2rem;
        text-align: right;
    }
    .msg-row.bot .msg-time { text-align: left; margin-left: 38px; }

    /* ── Typing indicator ── */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: .65rem 1rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 18px;
        border-bottom-left-radius: 4px;
        width: fit-content;
    }
    .typing-indicator span {
        width: 7px; height: 7px;
        background: #adb5bd;
        border-radius: 50%;
        display: inline-block;
        animation: bounce 1.2s infinite;
    }
    .typing-indicator span:nth-child(2) { animation-delay: .2s; }
    .typing-indicator span:nth-child(3) { animation-delay: .4s; }

    /* ── Input area ── */
    .chat-input-area {
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 16px 16px;
        padding: .85rem 1rem;
        display: flex;
        gap: .6rem;
        align-items: flex-end;
    }
    .chat-input-area textarea {
        flex: 1;
        border: 1px solid #dee2e6;
        border-radius: 22px;
        padding: .55rem 1rem;
        font-size: .9rem;
        resize: none;
        max-height: 120px;
        line-height: 1.5;
        outline: none;
        transition: border-color .2s;
    }
    .chat-input-area textarea:focus {
        border-color: #2d6a4f;
        box-shadow: 0 0 0 3px rgba(45,106,79,.12);
    }
    .btn-send {
        width: 42px; height: 42px;
        background: #2d6a4f;
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background .2s, transform .1s;
    }
    .btn-send:hover { background: #1b4332; }
    .btn-send:active { transform: scale(.93); }
    .btn-send:disabled { background: #adb5bd; cursor: not-allowed; }

    /* ── Suggestions rapides ── */
    .quick-suggestions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        padding: .6rem 1rem;
        background: #fff;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
    }
    .quick-btn {
        background: #f0f9f4;
        border: 1px solid #b7e4c7;
        color: #2d6a4f;
        border-radius: 20px;
        padding: .3rem .85rem;
        font-size: .8rem;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
    }
    .quick-btn:hover { background: #2d6a4f; color: #fff; border-color: #2d6a4f; }

    /* ── Animations ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes bounce {
        0%, 60%, 100% { transform: translateY(0); }
        30%           { transform: translateY(-6px); }
    }

    /* ── Responsive ── */
    @media (max-width: 576px) {
        .chat-wrapper { margin: 0; height: calc(100vh - 72px); border-radius: 0; }
        .chat-header  { border-radius: 0; }
        .chat-input-area { border-radius: 0; }
        .bubble { max-width: 88%; }
    }
</style>
@endsection

@section('content')
<div class="chat-wrapper">

    {{-- Header --}}
    <div class="chat-header">
        <div class="avatar">🌿</div>
        <div class="info">
            <h6>Basma</h6>
            <small>Assistante virtuelle Bosten • En ligne</small>
        </div>
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-outline-light rounded-pill" id="btn-clear"
                    title="Effacer la conversation">
                🗑️ <span class="d-none d-sm-inline">Effacer</span>
            </button>
        </div>
    </div>

    {{-- Zone messages --}}
    <div class="chat-messages" id="chat-messages">

        {{-- Message de bienvenue --}}
        <div class="msg-row bot" id="welcome-msg">
            <div class="bot-avatar">🌿</div>
            <div>
                <div class="bubble">
                    Bonjour <strong>{{ auth()->user()->name }}</strong> ! 👋<br>
                    Je suis <strong>Basma</strong>, votre assistante Bosten. Je peux vous aider avec :<br>
                    <br>
                    🌱 Notre catalogue de plantes<br>
                    📦 Le suivi de vos commandes<br>
                    💡 Des conseils jardinage<br>
                    <br>
                    Comment puis-je vous aider aujourd'hui ?
                </div>
                <div class="msg-time">{{ now()->format('H:i') }}</div>
            </div>
        </div>

        {{-- Historique --}}
        @foreach($history as $conv)
        <div class="msg-row user">
            <div>
                <div class="bubble">{{ $conv->message }}</div>
                <div class="msg-time">{{ $conv->created_at->format('H:i') }}</div>
            </div>
        </div>
        <div class="msg-row bot">
            <div class="bot-avatar">🌿</div>
            <div>
                <div class="bubble">{!! nl2br(e($conv->reply)) !!}</div>
                <div class="msg-time">{{ $conv->created_at->format('H:i') }}</div>
            </div>
        </div>
        @endforeach

    </div>

    {{-- Suggestions rapides --}}
    <div class="quick-suggestions" id="quick-suggestions">
        <button class="quick-btn" data-msg="Quels sont vos produits disponibles ?">🌿 Produits dispo</button>
        <button class="quick-btn" data-msg="Quel est le statut de ma dernière commande ?">📦 Ma commande</button>
        <button class="quick-btn" data-msg="Recommandez-moi une plante facile d'entretien">💡 Conseil plante</button>
        <button class="quick-btn" data-msg="Comment arroser mes plantes en été ?">💧 Arrosage</button>
    </div>

    {{-- Zone de saisie --}}
    <div class="chat-input-area">
        <textarea id="chat-input"
                  placeholder="Écrivez votre message..."
                  rows="1"
                  maxlength="500"></textarea>
        <button class="btn-send" id="btn-send" title="Envoyer">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
            </svg>
        </button>
    </div>

</div>

{{-- Token CSRF caché --}}
<meta name="csrf-token-value" content="{{ csrf_token() }}">
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const messagesEl  = document.getElementById('chat-messages');
    const inputEl     = document.getElementById('chat-input');
    const sendBtn     = document.getElementById('btn-send');
    const clearBtn    = document.getElementById('btn-clear');
    const suggestions = document.getElementById('quick-suggestions');
    const csrf        = document.querySelector('meta[name="csrf-token-value"]').content;
    const askUrl      = "{{ route('chatbot.ask') }}";
    let isLoading     = false;

    // ── Scroll vers le bas ──
    const scrollDown = () => {
        messagesEl.scrollTo({ top: messagesEl.scrollHeight, behavior: 'smooth' });
    };
    scrollDown();

    // ── Auto-resize textarea ──
    inputEl.addEventListener('input', () => {
        inputEl.style.height = 'auto';
        inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
    });

    // ── Envoyer avec Entrée (Shift+Entrée = nouvelle ligne) ──
    inputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ── Bouton envoyer ──
    sendBtn.addEventListener('click', sendMessage);

    // ── Suggestions rapides ──
    suggestions.querySelectorAll('.quick-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            inputEl.value = btn.dataset.msg;
            suggestions.style.display = 'none';
            sendMessage();
        });
    });

    // ── Effacer la conversation ──
    clearBtn.addEventListener('click', () => {
        if (!confirm('Effacer l\'historique de la conversation ?')) return;

        fetch("{{ route('chatbot.clear') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
            }
        }).then(() => {
            // Supprimer tous les messages sauf le bienvenue
            messagesEl.querySelectorAll('.msg-row:not(#welcome-msg)').forEach(el => el.remove());
            suggestions.style.display = 'flex';
        });
    });

    // ── Ajouter une bulle utilisateur ──
    function addUserBubble(text) {
        const now = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        const div = document.createElement('div');
        div.className = 'msg-row user';
        div.innerHTML = `
            <div>
                <div class="bubble">${escapeHtml(text)}</div>
                <div class="msg-time">${now}</div>
            </div>`;
        messagesEl.appendChild(div);
        scrollDown();
    }

    // ── Ajouter une bulle bot ──
    function addBotBubble(text) {
        const now = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        const div = document.createElement('div');
        div.className = 'msg-row bot';
        div.innerHTML = `
            <div class="bot-avatar">🌿</div>
            <div>
                <div class="bubble">${formatReply(text)}</div>
                <div class="msg-time">${now}</div>
            </div>`;
        messagesEl.appendChild(div);
        scrollDown();
    }

    // ── Indicateur de frappe ──
    function showTyping() {
        const div = document.createElement('div');
        div.className = 'msg-row bot';
        div.id = 'typing';
        div.innerHTML = `
            <div class="bot-avatar">🌿</div>
            <div class="typing-indicator">
                <span></span><span></span><span></span>
            </div>`;
        messagesEl.appendChild(div);
        scrollDown();
    }
    function hideTyping() {
        document.getElementById('typing')?.remove();
    }

    // ── Envoyer le message ──
    async function sendMessage() {
        const text = inputEl.value.trim();
        if (!text || isLoading) return;

        isLoading = true;
        sendBtn.disabled = true;
        inputEl.value = '';
        inputEl.style.height = 'auto';
        suggestions.style.display = 'none';

        addUserBubble(text);
        showTyping();

        try {
            const res = await fetch(askUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text }),
            });

            hideTyping();

            if (!res.ok) {
                throw new Error('Erreur serveur ' + res.status);
            }

            const data = await res.json();
            addBotBubble(data.reply ?? 'Désolé, je n\'ai pas pu répondre.');

        } catch (err) {
            hideTyping();
            addBotBubble('⚠️ Une erreur est survenue. Veuillez réessayer dans un instant.');
            console.error(err);
        } finally {
            isLoading = false;
            sendBtn.disabled = false;
            inputEl.focus();
        }
    }

    // ── Échapper le HTML ──
    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Formater la réponse (markdown basique) ──
    function formatReply(text) {
        return escapeHtml(text)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
});
</script>
@endsection
