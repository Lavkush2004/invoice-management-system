<?php
require_once __DIR__ . '/common.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['chatbot_csrf'])) {
    $_SESSION['chatbot_csrf'] = bin2hex(random_bytes(32));
}
$chatbotToken = $_SESSION['chatbot_csrf'];
$chatbotBaseUrl = APP_URL;
$chatbotAvatar = htmlspecialchars($chatbotBaseUrl . 'assets/images/chatbot-avatar.png', ENT_QUOTES, 'UTF-8');
$chatbotLabel = 'Login Help';
$chatbotGreeting = 'Hi! I can help with login, registration, and access problems.';
if (isset($_SESSION['admin_data'])) {
    $chatbotLabel = 'Admin Assistant';
    $chatbotGreeting = 'Hi! I can guide you through the admin invoice system.';
} elseif (isset($_SESSION['vendor_data'])) {
    $chatbotLabel = 'Vendor Assistant';
    $chatbotGreeting = 'Hi! I can guide you through vendor, customer, and invoice screens.';
} elseif (isset($_SESSION['customer_data'])) {
    $chatbotLabel = 'Customer Assistant';
    $chatbotGreeting = 'Hi! I can help you use invoice search and account screens.';
}
?>
<style>
    #invoice-chat-toggle { display:flex !important; align-items:center; justify-content:center; position:fixed !important; right:24px !important; bottom:24px !important; z-index:2147483647 !important; width:62px !important; height:62px !important; padding:4px !important; border:2px solid rgba(255,255,255,.8) !important; border-radius:50% !important; background:linear-gradient(135deg,#6d3df5,#1597e5) !important; color:#fff !important; line-height:1 !important; box-shadow:0 10px 25px rgba(49,50,155,.38) !important; transition:transform .2s ease, box-shadow .2s ease; }
    #invoice-chat-toggle:hover { transform:translateY(-3px) scale(1.04); box-shadow:0 14px 30px rgba(49,50,155,.48) !important; }
    #invoice-chat-toggle img { width:54px; height:54px; object-fit:contain; filter:drop-shadow(0 2px 3px rgba(0,0,0,.22)); }
    #invoice-chat-panel { display:none; position:fixed !important; right:24px !important; bottom:100px !important; z-index:2147483647 !important; width:370px; max-width:calc(100vw - 32px); border:1px solid rgba(102,112,255,.2); border-radius:18px; overflow:hidden; background:#fff; box-shadow:0 18px 45px rgba(37,35,101,.28); font-family:Arial,sans-serif; }
    #invoice-chat-panel.is-open { display:block; }
    .invoice-chat-head { display:flex; justify-content:space-between; align-items:center; padding:13px 16px; background:linear-gradient(135deg,#21145d,#5946d8 58%,#1997dc); color:#fff; }
    .invoice-chat-title { display:flex; align-items:center; gap:10px; min-width:0; }
    .invoice-chat-title img { width:42px; height:42px; object-fit:contain; border-radius:50%; background:rgba(255,255,255,.15); }
    .invoice-chat-title strong,.invoice-chat-title small { display:block; }
    .invoice-chat-title strong { font-size:15px; }
    .invoice-chat-title small { margin-top:2px; color:#dbeaff; font-size:11px; }
    .invoice-chat-title small:before { content:''; display:inline-block; width:7px; height:7px; margin-right:5px; border-radius:50%; background:#66f0ad; box-shadow:0 0 0 2px rgba(102,240,173,.2); }
    .invoice-chat-close { width:28px; height:28px; border:0; border-radius:50%; background:rgba(255,255,255,.14); color:#fff; font-size:22px; line-height:1; }
    #invoice-chat-messages { height:300px; overflow-y:auto; padding:16px; background:linear-gradient(180deg,#f7f8ff 0%,#fff 100%); }
    .invoice-chat-message { max-width:86%; margin:0 0 12px; padding:10px 13px; border-radius:14px; white-space:pre-wrap; word-wrap:break-word; font-size:13px; line-height:1.45; }
    .invoice-chat-user { margin-left:auto; border-bottom-right-radius:4px; background:linear-gradient(135deg,#5d40e6,#198edb); color:#fff; box-shadow:0 4px 10px rgba(58,72,190,.18); }
    .invoice-chat-bot { border-bottom-left-radius:4px; background:#fff; color:#283247; box-shadow:0 3px 12px rgba(33,35,78,.08); }
    #invoice-chat-form { display:flex; gap:8px; padding:12px; border-top:1px solid #edf0f7; background:#fff; }
    #invoice-chat-input { flex:1; min-width:0; border:1px solid #dde2f0; border-radius:10px; padding:10px 12px; outline:0; font-size:13px; }
    #invoice-chat-input:focus { border-color:#6b60de; box-shadow:0 0 0 3px rgba(103,94,222,.12); }
    #invoice-chat-send { min-width:58px; border:0; border-radius:10px; padding:8px 12px; background:linear-gradient(135deg,#5d40e6,#198edb); color:#fff; font-weight:600; }
    #invoice-chat-send:disabled { opacity:.6; }
</style>
<button id="invoice-chat-toggle" type="button" aria-label="Open chatbot" aria-expanded="false"><img src="<?php echo $chatbotAvatar; ?>" alt=""></button>
<section id="invoice-chat-panel" aria-label="Invoice system chatbot" aria-hidden="true">
    <div class="invoice-chat-head"><div class="invoice-chat-title"><img src="<?php echo $chatbotAvatar; ?>" alt=""><div><strong><?php echo htmlspecialchars($chatbotLabel, ENT_QUOTES, 'UTF-8'); ?></strong><small>Online and ready to help</small></div></div><button class="invoice-chat-close" type="button" aria-label="Close chatbot">&times;</button></div>
    <div id="invoice-chat-messages" aria-live="polite"><div class="invoice-chat-message invoice-chat-bot"><?php echo htmlspecialchars($chatbotGreeting, ENT_QUOTES, 'UTF-8'); ?></div></div>
    <form id="invoice-chat-form"><input id="invoice-chat-input" type="text" maxlength="1000" autocomplete="off" placeholder="Ask a question..." aria-label="Chat message"><button id="invoice-chat-send" type="submit">Send</button></form>
</section>
<script>
    (function () {
        var panel = document.getElementById('invoice-chat-panel'), toggle = document.getElementById('invoice-chat-toggle'), close = panel.querySelector('.invoice-chat-close'), form = document.getElementById('invoice-chat-form'), input = document.getElementById('invoice-chat-input'), messages = document.getElementById('invoice-chat-messages'), send = document.getElementById('invoice-chat-send');
        function setOpen(open) { panel.classList.toggle('is-open', open); panel.setAttribute('aria-hidden', String(!open)); toggle.setAttribute('aria-expanded', String(open)); if (open) input.focus(); }
        function addMessage(text, kind) { var entry = document.createElement('div'); entry.className = 'invoice-chat-message invoice-chat-' + kind; entry.textContent = text; messages.appendChild(entry); messages.scrollTop = messages.scrollHeight; return entry; }
        toggle.addEventListener('click', function () { setOpen(!panel.classList.contains('is-open')); });
        close.addEventListener('click', function () { setOpen(false); });
        form.addEventListener('submit', function (event) {
            event.preventDefault(); var message = input.value.trim(); if (!message) return;
            addMessage(message, 'user'); input.value = ''; input.disabled = true; send.disabled = true;
            var waiting = addMessage('Thinking...', 'bot');
            fetch('<?php echo $chatbotBaseUrl; ?>chatbot.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({message:message, csrf_token:'<?php echo $chatbotToken; ?>'}) })
                .then(function (response) { return response.json(); })
                .then(function (data) { waiting.textContent = data.answer || data.error || 'Something went wrong. Please try again.'; })
                .catch(function () { waiting.textContent = 'Could not reach the chatbot. Please try again.'; })
                .then(function () { input.disabled = false; send.disabled = false; input.focus(); messages.scrollTop = messages.scrollHeight; });
        });
    }());
</script>
