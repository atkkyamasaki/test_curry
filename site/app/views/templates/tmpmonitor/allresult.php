<h2>MonitorBox暫定監視ツール</h2>

<div class="basic_info">
<p>"Start"ボタンをクリックするとサマリーページへ移動します。</p>
<p>※ 各MonitorBoxへの処理は本ページで実行しますので、サマリーページで情報を確認する際は<br>　 本ページで処理を実行したままサマリーページで状況を確認してください。</p>
</div>


<button id="start" class="button">Start</button>
<button id="stop" class="button">Stop</button>

<?php $i = 0; ?>
<?php foreach ($mbs_info as $key1 => $val1): ?>
  <?php foreach ($val1 as $key2 => $val2): ?>

    
    <!-- <?php var_dump($val2); ?> -->

    <div class="basic_info">
      <h3><?php echo $val2['groupname']; ?>  (<?php echo $val2['mbname']; ?>:<span><?php echo $val2['natip'] ;?></span>)</h3>

      <div class="basic_info">
        <h3>Ping</h3>

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
            <tbody id="ping_change_tbody<?php echo $i; ?>">
            <!-- jQuery で情報を取得 -->
            </tbody>
          </table>

      </div>


      <div class="basic_info">
        <h3>SNMP TRAP</h3>

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
            <tbody id="snmptrap_change_tbody<?php echo $i; ?>">
            </tbody>
          </table>

      </div>
      
    </div>

  <?php $i = $i + 1; ?>
  <?php endforeach; ?>
<?php endforeach; ?>

<div style="visibility:hidden">
  <span id="natips">
    <?php foreach ($mbs_ip as $val): ?>
    <?php echo $val; ?>,
    <?php endforeach ?>
  </span>
</div>