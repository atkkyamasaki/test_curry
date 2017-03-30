<h2>MonitorBox暫定監視ツール</h2>

<!-- <button id="summary_start" class="button">Start</button>
<span class="class_countdown">CountDown:<span id="summary_countdown" class="do_countdown"></span></span>
<button id="summary_stop" class="button">Stop</button>
 -->
<div id="summary_get_ajax" class="ajax_update_button">
  <i class="fa fa-repeat" aria-hidden="true"></i>
</div>
<button id="summary_start" class="button">Start</button>
<span class="class_countdown">CountDown:
  <select id="summary_interval" name="interval">
    <option value="30">30s</option>
    <option value="60" selected>60s</option>
    <option value="180">180s</option>
    <option value="300">300s</option>
  </select>
</span>
<span id="summary_countdown" class="do_countdown">
</span>
<button id="summary_stop" class="button">Stop</button>

<table>
  <thead>
    <tr>
      <th>MonitorBox名</th>
      <th>Ping結果  (NG時：失敗率)</th>
      <th>SNMP Trap結果</th>
    </tr>
  </thead>
  <tbody>


<?php $i = 0; ?>
<?php foreach ($mbs_info as $key1 => $val1): ?>
  <?php foreach ($val1 as $key2 => $val2): ?>

    
    <!-- <?php var_dump($val2); ?> -->

    <tr>
      <td><?php echo $val2['groupname']; ?>  (<?php echo $val2['mbname']; ?>:<span><?php echo $val2['natip'] ;?></span>)</td>
      <td id="pingresult<?php echo $i; ?>"></td>
      <td id="snmptrapresult<?php echo $i; ?>"></td>
    </tr>
  <?php $i = $i + 1; ?>
  <?php endforeach; ?>
<?php endforeach; ?>


  </tbody>
</table>

<div class="basic_info">
<p>[ 利用方法 ]</p>
<p>"Start"ボタンをクリックすると情報が自動更新されます。</p>
<p>※ 各MonitorBoxへの処理は "tmpmonitor/allresult" ページで実行しますので、サマリーページで情報を確認する際は<br>
　 事前に "tmpmonitor/allresult" ページで処理を実行しておいてください。</p>
</div>

<div style="visibility:hidden">
  <span id="natips">
    <?php foreach ($mbs_ip as $val): ?>
    <?php echo $val; ?>,
    <?php endforeach ?>
  </span>
</div>

<div class="all_loading is-hide">
  <div class="all_loading_icon"></div>
</div>