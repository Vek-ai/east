<!-- Chat Widget JavaScript - Place this before closing </body> tag -->
<?php if (isset($_SESSION['userid']) && isset($_SESSION['fullname'])): ?>
<script src="StaffChatter/js/chat-widget.js"></script>
<script>
    const chatWidget = new ChatWidget({
        currentUserId: <?php echo $_SESSION['userid']; ?>,
        currentUserName: '<?php echo htmlspecialchars($_SESSION['fullname']); ?>'
    });
</script>
<?php endif; ?>
