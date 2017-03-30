<h2>ホストの監視データ取得状況一覧<br>
[<?php echo $zbx_hostgroup['0']['name']; ?>]</h2>

<!-- host 絞り込み用チェックボックス -->
<form method="GET" action="/datacheck/precheck">
<input type="hidden" name="groupids" value="<?php echo $query['groupids']; ?>">

<!-- Host 件数 -->
<p><?php echo count($new_hosts); ?>件</p>

<!-- ヘルプの挿入 -->
<div class="toolchip_help">ヘルプ
    <span> 
        <?php echo $help; ?>
    </span>
</div>

<!-- host の item 取得状況 Table 作成 -->

<?php if (!empty($new_hosts)): ?>
<table id="sorter">
  <thead>
    <tr>
      <th>Filter</th>
      <th>ホストID</th>
      <th>ホスト名</th>
      <th>監視データ取得状況</th>
      <th>Zabbix agent(0)</th>
      <th>SNMPv1 agent(1)</th>
      <th>Zabbix trapper(2)</th>
      <th>simple check(3)</th>
      <th>SNMPv2 agent(4)</th>
      <th>Zabbix internal(5)</th>
      <th>SNMPv3 agent(6)</th>
      <th>Zabbix agent (active)(7)</th>
      <th>Zabbix aggregate(8)</th>
      <th>web item(9)</th>
      <th>external check(10)</th>
      <th>database monitor(11)</th>
      <th>IPMI agent(12)</th>
      <th>SSH agent(13)</th>
      <th>TELNET agent(14)</th>
      <th>calculated(15)</th>
      <th>JMX agent(16)</th>
      <th>SNMP trap(17)</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($new_hosts as $val): ?>
    <tr>
      <td><label><input type="checkbox" name="hostids[]" value="<?php echo $val['hostid']; ?>" ></label></td>    
      <td><?php echo $val['hostid']; ?></td>
      <td><a href="/datacheck/hostcheck?hostids=<?php echo $val['hostid']; ?>" target=_blank><?php echo $val['hostname']; ?></a><?php if($val['status'] === '1') { echo '<em>  (無効)</em>';}?></td>
      <td><?php echo $val['total']; ?></td>
      <td><?php echo $val['0']; ?></td>
      <td><?php echo $val['1']; ?></td>
      <td><?php echo $val['2']; ?></td>
      <td><?php echo $val['3']; ?></td>
      <td><?php echo $val['4']; ?></td>
      <td><?php echo $val['5']; ?></td>
      <td><?php echo $val['6']; ?></td>
      <td><?php echo $val['7']; ?></td>
      <td><?php echo $val['8']; ?></td>
      <td><?php echo $val['9']; ?></td>
      <td><?php echo $val['10']; ?></td>
      <td><?php echo $val['11']; ?></td>
      <td><?php echo $val['12']; ?></td>
      <td><?php echo $val['13']; ?></td>
      <td><?php echo $val['14']; ?></td>
      <td><?php echo $val['15']; ?></td>
      <td><?php echo $val['16']; ?></td>
      <td><?php echo $val['17']; ?></td>
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
