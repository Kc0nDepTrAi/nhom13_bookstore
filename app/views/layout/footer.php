
<?php if (isset($_SESSION['username'])): ?>
<a href="<?= BASE_URL ?>/chat.php" class="chat-float" id="chatFloat">
    💬
    <span class="chat-badge" id="chatBadge"></span>
</a>
<script>
(function checkUnread() {
    fetch('<?= BASE_URL ?>/public/chat_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_unread_count'
    }).then(r => r.json()).then(d => {
        if (d.status === 'success' && d.unread_count > 0) {
            const b = document.getElementById('chatBadge');
            if (b) { b.textContent = d.unread_count > 9 ? '9+' : d.unread_count; b.style.display = 'flex'; }
        }
    }).catch(() => {});
})();
</script>
<?php endif; ?>
</body>
</html>
