<?php if (isset($pre)): ?>
    <?php echo $pre; ?>
<?php endif; ?>
<?php if (isset($form)): ?>
    <?php echo $form; ?>
<?php endif; ?>
<?php if (isset($debug)): ?>
    <p>GET レスポンスの複数ヘッダに対応していないため一時的に $responses をそのまま var_dump してる。</p>
    複数ヘッダ時のエラーメッセージ ↓ <?php echo isset($debug_message) ? '<p>' . $debug_message . '</p>' : 'エラーではない'; ?>
    <pre>
    <?php var_dump($debug); ?>
    </pre>
<?php endif; ?>
<?php if (isset($table)): ?>
    <?php echo $table; ?>
<?php endif; ?>
