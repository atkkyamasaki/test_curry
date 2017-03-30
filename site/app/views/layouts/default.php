<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <!--[if lt IE 9]>
    <script src="<?php echo $request['base_path']; ?>/js/html5shiv.min.js<?php echo $helper->getFileQuery('html5shiv.min.js', 'js'); ?>"></script>
    <![endif]-->
    <?php foreach ($stylesheets as $css) : ?>
        <link rel="stylesheet" href="<?php echo $request['base_path']; ?>/css/<?php echo $css ?><?php echo $helper->getFileQuery($css, 'css'); ?>">
    <?php endforeach; ?>
    <title><?php echo $helper->escape($page_title); ?></title>
  </head>
  <body>
    <header class="header">
      <h1><a href="<?php echo $helper->createUrl('/'); ?>">Net.Monitor Console Service - Beta</a></h1>
      <?php if (isset($user)): ?>
          <p><i class="fa fa-user" aria-hidden="true"></i>Login User: <?php echo isset($user['alias']) ? $helper->escape($user['alias']) : 'Unknown'; ?>（<a href="<?php echo $helper->createUrl('logout'); ?>"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a>）</p>
      <?php endif; ?>
      <?php if (isset($endpoint)): ?>
          <p><i class="fa fa-server" aria-hidden="true"></i>Endpoint: <?php echo $this->helper->escape($endpoint); ?>（<a href="<?php echo $helper->createUrl('logout'); ?>"><i class="fa fa-refresh" aria-hidden="true"></i>Change</a>）</p>
      <?php endif; ?>
    </header>
    <?php if (isset($user)): ?>
        <nav class="global-navigation">
          <?php if (isset($resources)): ?>
              <ul>
                <?php foreach ($resources as $resource): ?>
                    <li><a href="<?php echo $helper->createUrl(['api', $resource]); ?>"<?php if ($resource === lcfirst($page_title)): ?> class="is-active"<?php endif; ?>><?php echo $helper->escape(ucfirst($resource)); ?></a></li>
                <?php endforeach; ?>
              </ul>
          <?php endif; ?>
          <?php if (isset($menus)): ?>
              <ul>
                <?php foreach ($menus as $menu): ?>
                    <li><a href="<?php echo $helper->createUrl($menu); ?>"<?php if ($menu === lcfirst($page_title)): ?> class="is-active"<?php endif; ?>><?php echo $helper->escape(ucfirst($menu)); ?></a></li>
                <?php endforeach; ?>
              </ul>
          <?php endif; ?>
        </nav>
    <?php endif; ?>
    <article class="article">
      <h2><?php echo $helper->escape($page_title); ?></h2>
      <?php if (isset($message)): ?>
          <?php var_dump($message); ?>
      <?php endif; ?>
      <?php echo $inner_contents; ?>
    </article>
    <footer class="footer">
      <p>gonokami@allied-telesis.co.jp</p>
    </footer>
    <?php foreach ($javascripts as $js) : ?>
        <script src="<?php echo $request['base_path']; ?>/js/<?php echo $js ?><?php echo $helper->getFileQuery($js, 'js'); ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
