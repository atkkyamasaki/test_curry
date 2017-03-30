<h3>検索</h3>
<?php if (isset($search_form)): ?>
    <?php echo $search_form; ?>
<?php endif; ?>
<h3>送信</h3>
<?php if (isset($send_syslog_form)): ?>
    <?php echo $send_syslog_form; ?>
<?php endif; ?>
<h3>保存</h3>
<?php if (isset($save_form)): ?>
    <?php echo $save_form; ?>
<?php endif; ?>
<h3>一覧</h3>
<?php if (isset($paginator)): ?>
    <nav class="paginator">
      <p><?php echo $this->helper->escape($paginator['current_display']); ?>件 / <?php echo $this->helper->escape($paginator['results_count']); ?>件</p>
      <ol>
        <?php if ($paginator['previous_url']): ?>
            <li><a href="<?php echo $helper->createUrl($path, ['page' => $paginator['previous_url']]); ?><?php if ($paginator['query']): ?><?php echo $paginator['query']; ?><?php endif; ?>"><<前のページ</a></li>
        <?php else: ?>
            <li><<前のページ</li>
        <?php endif; ?>
          <?php if ($paginator['first_url']): ?><li><a href="<?php echo $helper->createUrl($path, ['page' => $paginator['first_url']]); ?><?php if ($paginator['query']): ?><?php echo $paginator['query']; ?><?php endif; ?>"><?php echo $paginator['first_url']; ?></a></li><?php endif; ?>
        <?php if ($paginator['first_delimiter']): ?><li>...</li><?php endif; ?>
        <?php foreach ($paginator['pages'] as $page): ?>
            <?php if ($page['current']): ?>
                <li><?php echo $page['page']; ?></li>
            <?php else: ?>
                <li><a href="<?php echo $helper->createUrl($path, ['page' => $page['page']]); ?><?php if ($paginator['query']): ?><?php echo $paginator['query']; ?><?php endif; ?>"><?php echo $page['page']; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
          <?php if ($paginator['last_delimiter']): ?><li>...</li><?php endif; ?>
          <?php if ($paginator['last_url']): ?>
            <li><a href="<?php echo $helper->createUrl($path, ['page' => $paginator['last_url']]); ?><?php if ($paginator['query']): ?><?php echo $paginator['query']; ?><?php endif; ?>"><?php echo $paginator['last_url']; ?></a></li>
        <?php endif; ?>
        <?php if ($paginator['next_url']): ?>
            <li><a href="<?php echo $helper->createUrl($path, ['page' => $paginator['next_url']]); ?><?php if ($paginator['query']): ?><?php echo $paginator['query']; ?><?php endif; ?>">次のページ>></a></li>
        <?php else: ?>
            <li>次のページ>></li>
        <?php endif; ?>
      </ol>
    </nav>
<?php endif; ?>
<?php if (isset($syslog_table)): ?>
    <?php echo $syslog_table; ?>
<?php endif; ?>
