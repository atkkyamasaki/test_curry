<h2>監視設定確認ツール</h2>

<!-- hostgroup 選択用プルダウン -->
<form method="GET" enctype="multipart/form-data">
  <label for="groupids">HostGroupを選択してください</label>
    <select name="groupids">
      <?php foreach ($zbx_hostgroups_in_host as $val): ?>
        <option value="<?php echo $val['groupid']; ?>"><?php echo $val['name'];?></option>
      <?php endforeach; ?>
    </select>
<input type="submit" />
</form>

<!-- hostgroup 情報から各種機能への Link Table を作成
(hostgroup = null の場合は "No Hostgroup" を表示) -->

<?php if (!empty($zbx_hostgroups)): ?>
<table>
  <tr>
    <th>グループ名</th>
    <th>監視データ取得情報一覧</th> 
    <th>監視設定情報一覧</th> 
  </tr>
  <?php foreach ($zbx_hostgroups as $val1): ?>
  <tr>
    <td><?php echo $val1['name'] . " (groupid:" . $val1['groupid'] . ")"; ?></td>    
    <td><button><a href="/datacheck/precheck?groupids=<?php echo $val1['groupid']; ?>" type=button target=_blank>GO</a></button></td>
    <td><button><a href="/datacheck/configcheck?groupids=<?php echo $val1['groupid']; ?>" type=button target=_blank>GO</a></button></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php else: ?>
<p>No Hostgroup</php>
<?php endif; ?>

