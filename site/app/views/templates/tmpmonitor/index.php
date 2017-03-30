<h2>MonitorBox暫定監視ツール</h2>

<!-- hostgroup 選択用プルダウン -->
<form method="GET" enctype="multipart/form-data">
  <h3>HostGroupを選択してください</h3>
    <label><a href="tmpmonitor/allresult">すべて選択</a></label>
    <?php foreach ($zbx_hostgroups_in_host as $val): ?>
      <label>
        <input type="radio" name="groupids" value="<?php echo $val['groupid']; ?>"><?php echo $val['name'];?>
      </label>
    <?php endforeach; ?>
<input type="submit" />
</form>

<!-- hostgroup 選択用プルダウン -->
<?php if (!empty($mb_info)): ?>
<table>
  <tr>
    <th>グループ名</th>
    <th>MonitorBox</th>
    <th>MonitorBox WebUIへLogin</th> 
    <th>客先監視機器状態の確認</th> 
  </tr>
  <?php foreach ($mb_info as $key => $val): ?>
  <tr>
    <td><?php echo $val['groupname'] . " (groupid:" . $val['groupid'] . ")"; ?></td>    
    <td><?php echo $val['mbname']; ?></td>    
    <td><button><a href="http://<?php echo $val['natip']; ?>:3000/login" type=button target=_blank>GO</a></button></td>
    <td><button><a href="tmpmonitor/result?groupids=<?php echo $val['groupid'] . '&mbs=' . $val['mb']; ?>" type=button target=_blank>GO</a></button></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php else: ?>
<p>No Information about MonitorBox...</php>
<?php endif; ?>

