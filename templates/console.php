<div class="page-header">
    <h2><?php echo t('Console') ?></h2>
    <ul>
        <li><a href="?action=console"><?php echo t('refresh') ?></a></li>
        <li><a href="?action=flush-console"><?php echo t('flush messages') ?></a></li>
    </ul>
</div>

<?php if (empty($content)): ?>
    <p class="alert alert-info"><?php echo t('No message') ?></p>
<?php else: ?>
    <pre id="console"><code><?php echo Helper\escape($content) ?></code></pre>
<?php endif ?>
