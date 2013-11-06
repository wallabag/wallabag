<div class="page-header">
    <h2><?= t('Console') ?></h2>
    <ul>
        <li><a href="?action=console"><?= t('refresh') ?></a></li>
        <li><a href="?action=flush-console"><?= t('flush messages') ?></a></li>
    </ul>
</div>

<?php if (empty($content)): ?>
    <p class="alert alert-info"><?= t('No message') ?></p>
<?php else: ?>
    <pre id="console"><code><?= Helper\escape($content) ?></code></pre>
<?php endif ?>
