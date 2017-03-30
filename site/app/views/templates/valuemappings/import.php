<p>インポートしたいJSON文字列をペーストして送信をクリックしてください。</p>
<p><a href="<?php echo $helper->createUrl(['valuemappings', 'export']); ?>">エクスポート（JSONを入手）</a><p>
<form method="post" action="<?php echo $helper->createUrl(['valuemappings', 'import']); ?>">
  <p><input type="submit"></p>
  <textarea cols="100" rows="50" name="json"></textarea>
  <p><input type="submit"></p>
</form>