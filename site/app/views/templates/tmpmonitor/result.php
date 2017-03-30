<h2>MonitorBox暫定監視ツール</h2>
<?php foreach ($mb_info as $val): ?>
<ul>
    <li>MonitorBox:<?php echo $val['mbname'] ;?></li>
    <li>IP:<span id="natip"><?php echo $val['natip'] ;?></span></li>
</ul>
<?php endforeach; ?>

<div class="basic_info">
  <h3>Ping</h3>
  <button id="pingstart" class="button">Start</button>
  <button id="pingstop" class="button">Stop</button>
  <button id="ping_table_clear" class="button">Clear</button>

  <table>
    <thead>
      <tr>
        <th>ホスト名</th>
        <th>IP</th>
        <th>直近のステータス</th>
        <th>実施回数</th>
        <th>NG回数</th>
        <th>失敗率</th>
        <th>最短時間(ms)</th>
        <th>最大時間(ms)</th>
        <th>平均時間(ms)</th>
      </tr>
    </thead>
    <tbody id="ping_change_tbody">
    <!-- jQuery で情報を取得 -->
    </tbody>
  </table>
</div>


<div class="basic_info">
  <h3>SNMP TRAP</h3>
  <h3>実行結果<span class="snmptrap_time">  [現在日時：<?php echo date( "Y-m-d H:i:s" ) ?>]</span></h3>
  <div id="result_get_ajax" class="ajax_update_button">
    <i class="fa fa-repeat" aria-hidden="true"></i>
  </div>
  <button id="result_start" class="button">Start</button>
  <span class="class_countdown">CountDown:
    <select id="result_interval" name="interval">
      <option value="30">30s</option>
      <option value="60" selected>60s</option>
      <option value="180">180s</option>
      <option value="300">300s</option>
    </select>
  </span>
  <span id="result_countdown" class="do_countdown">
  </span>
  <button id="result_stop" class="button">Stop</button>


  <table class="snmptrap_table">
    <thead>
      <tr>
        <th>Month</th>
        <th>Day</th>
        <th>Time</th>
        <th>Host</th>
        <th>IP</th>
        <th>Trap Item</th>
        <th>Message</th>
      </tr>
    </thead>
    <tbody id="snmptrap_change_tbody">
    </tbody>
  </table>

</div>