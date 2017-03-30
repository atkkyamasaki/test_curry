<h2>ホストの監視データ取得状況確認<br>
[<?php echo $zbx_hostname;?>]</h2>
<h3>現在のUnix時刻：<?php echo $unix_time; ?></h3>

<!-- host 絞り込み用チェックボックス -->
<form method="GET" action="/datacheck/hostcheck">
<input type="hidden" name="hostids" value="<?php echo $query['hostids']; ?>">

<!-- item 件数 -->
<p><?php echo count($new_items); ?>件</p>

<!-- ヘルプの挿入 -->
<div class="toolchip_help">ヘルプ
    <span> 
        <?php echo $help; ?>
    </span>
</div>

<!-- host の item 毎のデータ取得状況 Table 作成 -->
<?php if (!empty($new_items)): ?>
<table id="sorter">
  <thead>
    <tr>
      <th>Filter</th>
      <th>アイテム名</th>
      <th>タイプ</th>
      <th>データ取得間隔</th>
      <th>最新データの取得時刻</th>
      <th>最新のデータ</th>
      <th>最新データ取得からの経過時間</th>
      <th>判定結果</th>
      <th>エラー</th>
      <th>ホストID</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($new_items as $val): ?>
    <tr>
      <td><label><input type="checkbox" name="itemids[]" value="<?php echo $val['itemid']; ?>" ></label></td>    
      <td><?php echo $val['name'] . " (itemid:" . $val['itemid'] . ")"; ?></td>    
      <td><?php echo $val['type']; ?></td>
      <td><?php echo $val['delay'] ?></td>
      <td><?php echo $val['lastclock']; ?></td>
      <td><?php echo $val['lastvalue']; ?></td>
      <td><?php echo $val['counttime']; ?></td>
      <td><?php echo $val['htmltag_decision']; ?></td>
      <td><?php echo $val['error']; ?></td>
      <td><?php echo $val['hostid']; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p>No Items Found.</php>
<?php endif; ?>

<p><input type="submit" value="絞込み"></p>

<!-- item ではなく history を利用して再判定 -->
<?php if (empty($query['historymode'])): ?>
<?php $query = array_merge($query, array('historymode' => '1')); ?>
<input type="button" onclick="location.href='<?php echo $helper->createUrl($url_BaseUrl . $url_Path, $query); ?>' "value="ヒストリ検索">
<?php else: ?>
<?php unset($query['historymode']); ?>
<input type="button" onclick="location.href='<?php echo $helper->createUrl($url_BaseUrl . $url_Path, $query); ?>' "value="ヒストリ検索解除">
<?php endif; ?>


