<h2>ホストの監視設定状況一覧<br>
[<?php echo $zbx_hostgroup['0']['name']; ?>]</h2>

<!-- Host 件数 -->
<p>Host:<?php echo count($monitor_lists); ?>件</p>

<!-- CSV エクスポートボタン -->
<button>
  <a href="/csv/config.csv" type="button" download="config.csv">CSV Download</a>
</button>

<!-- host の item 取得状況 Table 作成 -->
<?php if (!empty($monitor_lists)): ?>
<table id="sorter">
  <thead>
  <tr>
    <th>ホストID</th>
    <th>ホスト名</th>
    <th>有効/無効(ホスト)</th>
    <th>IP Address</th>
    <th>Port番号</th>
    <th>ProxyID</th>
    <th>Proxy名</th>
    <th>マクロ[Community名(SNMP)]</th>
    <th>アプリケーションID</th>
    <th>アプリケーション名</th>
    <th>アイテムID</th>
    <th>アイテム名</th>
    <th>アイテムタイプ</th>
    <th>有効/無効(アイテム)</th>
    <th>更新間隔(アイテム)[s]</th>
    <th>保存時の計算</th>
    <th>乗数</th>
    <th>単位</th>
    <th>ヒストリ保存期間[day]</th>
    <th>トレンド保存期間[day]</th>
    <th>データタイプ</th>
    <th>トリガーID</th>
    <th>トリガー名</th>
    <th>有効/無効(トリガー)</th>
    <th>条件式</th>
    <th>深刻度</th>
    <th>ディスカバリID</th>
    <th>ディスカバリ名</th>
    <th>有効/無効(ディスカバリ)</th>
    <th>更新間隔(ディスカバリ)[s]</th>
  </tr>
  </thead>

  <tbody>
  <?php foreach ($monitor_lists as $val1): ?>
  <?php foreach ($val1 as $val2): ?>

  <tr>

    <!-- ホストID -->
    <td>
    <?php echo $val2['hostid']; ?>
    </td>


    <!-- ホスト名 -->
    <td>
    <?php echo $val2['host_info']['0']['host']; ?>
    </td>


    <!-- 有効/無効(ホスト) -->
    <td>
    <?php 
        if ($val2['host_info']['0']['status'] === '0') {
            echo '<span class="enable">有効</span>';
        } else {
            echo '<span class="disable">無効</span>';
        }
    ?>
    </td>


    <!-- IP Address -->
    <td>
    <?php
        if (!empty($val2['hostinterface_info']['0']['ip'])) {
            echo $val2['hostinterface_info']['0']['ip'];
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- Port番号 -->
    <td>
    <?php
        if (!empty($val2['hostinterface_info']['0']['port'])) {
            echo $val2['hostinterface_info']['0']['port'];
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- ProxyID -->
    <td>
    <?php
        if (!empty($val2['proxy_info']['0']['proxyid'])) {
            echo $val2['proxy_info']['0']['proxyid'];
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- Proxy名 -->
    <td>
    <?php
        if (!empty($val2['proxy_info']['0']['host'])) {
            echo $val2['proxy_info']['0']['host'];
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- マクロ[Community名(SNMP)] -->
    <!-- 複数あり -->
    <td>
    <?php
        if (!empty($val2['host_info']['0']['macros'])) {
            foreach ($val2['host_info']['0']['macros'] as $val_macros) {
                echo $val_macros['macro'] . ':' . $val_macros['value']; 
                echo '<br>';
            }
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- アプリケーションID -->
    <!-- 複数あり(複数ある場合は設定不備の可能性あり) -->
    <td>
    <?php
        if (!empty($val2['application_info'])) {
            foreach ($val2['application_info'] as $val_applications) {
                echo $val_applications['applicationid'];
                echo '<br>';
            }
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- アプリケーション名 -->
    <!-- 複数あり(複数ある場合は設定不備の可能性あり) -->
    <td>
    <?php
        if (!empty($val2['application_info'])) {
            foreach ($val2['application_info'] as $val_applications) {
                echo $val_applications['name'];
                echo '<br>';
            }
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- アイテムID -->
    <td>
    <?php echo $val2['itemid']; ?>
    </td>


    <!-- アイテム名 -->
    <td>
    <?php echo $val2['name']; ?>
    </td>


    <!-- アイテムタイプ -->
    <td>
    <?php
        switch ($val2['type']) {
            case '0':
                echo '<span class="zabbix_agent">Zabbix agent</span>' ;
                break;
            case '1':
                echo '<span class="snmpv1_agent">SNMPv1 agent</span>' ;
                break;
            case '2':
                echo '<span class="zabbix_trapper">Zabbix trapper</span>' ;
                break;
            case '3':
                echo '<span class="simple_check">simple check</span>' ;
                break;
            case '4':
                echo '<span class="snmpv2_agent">SNMPv2 agent</span>' ;
                break;
            case '5':
                echo '<span class="zabbix_internal">Zabbix internal</span>' ;
                break;
            case '6':
                echo '<span class="snmpv3_agent">SNMPv3 agent</span>' ;
                break;
            case '7':
                echo '<span class="zabbix_agent_active">Zabbix agent(active)</span>' ;
                break;
            case '8':
                echo '<span class="zabbix_aggregate">Zabbix aggregate</span>' ;
                break;
            case '9':
                echo '<span class="web_item">web item</span>' ;
                break;
            case '10':
                echo '<span class="external_check">external check</span>' ;
                break;
            case '11':
                echo '<span class="database_monitor">database monitor</span>' ;
                break;
            case '12':
                echo '<span class="ipmi_agent">IPMI agent</span>' ;
                break;
            case '13':
                echo '<span class="ssh_agent">SSH agent</span>' ;
                break;
            case '14':
                echo '<span class="telnet_agent">TELNET agent</span>' ;
                break;
            case '15':
                echo '<span class="calculated">calculated</span>' ;
                break;
            case '16':
                echo '<span class="jmx_agent">JMX agent</span>' ;
                break;
            case '17':
                echo '<span class="snmp_trap">SNMP trap</span>' ;
                break;
        }
    ?>
    </td>


    <!-- 有効/無効(アイテム) -->
    <td>
    <?php
        if ($val2['status'] === '0') {
            echo '<span class="enable">有効</span>';
        } else {
            echo '<span class="disable">無効</span>';
        }
    ?>
    </td>


    <!-- 更新間隔(アイテム)[s] -->
    <td>
    <?php echo $val2['delay']; ?>
    </td>


    <!-- 保存時の計算 -->
    <td>
    <?php
        switch ($val2['delta']) {
            case '0':
                echo 'なし' ;
                break;
            case '1':
                echo '差分/時間' ;
                break;
            case '2':
                echo '差分' ;
                break;
        }
    ?>
    </td>


    <!-- 乗数 -->
    <td>
    <?php
        if (!empty($val2['formula'])) {
            echo $val2['formula'];
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- 単位 -->
    <td>
    <?php
        if (!empty($val2['units'])) {
            echo $val2['units'];
        } else {
            echo '-';
        } 
    ?>
    </td>


    <!-- ヒストリ保存期間[day] -->
    <td>
    <?php echo $val2['history']; ?>
    </td>


    <!-- トレンド保存期間[day] -->
    <td>
    <?php echo $val2['trends']; ?>
    </td>


    <!-- データタイプ -->
    <td>
    <?php
        switch ($val2['value_type']) {
            case '0':
                echo '<span class="numeric_float">数値(浮動少数)</span>' ;
                break;
            case '1':
                echo '<span class="character">文字列</span>' ;
                break;
            case '2':
                echo '<span class="log">ログ</span>' ;
                break;
            case '3':
                echo '<span class="numeric_unsigned">数値(整数)</span>' ;
                break;
            case '4':
                echo '<span class="text">テキスト</span>' ;
                break;
        }
    ?>
    </td>


    <!-- トリガーID -->
    <!-- 複数あり -->
    <td>
    <?php
        if (!empty($val2['trigger_info'])) {
            foreach ($val2['trigger_info'] as $val_triggers) {
                echo $val_triggers['triggerid'];
                echo '<br>';
            }
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- トリガー名 -->
    <!-- 複数あり -->
    <td>
    <?php
        if (!empty($val2['trigger_info'])) {
            foreach ($val2['trigger_info'] as $val_triggers) {
                echo $val_triggers['description'];
                echo '<br>';
            }
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- 有効/無効(トリガー) -->
    <!-- 複数あり -->
    <td>
    <?php
        if (!empty($val2['trigger_info'])) {
            foreach ($val2['trigger_info'] as $val_triggers) {
                if ($val_triggers['status'] === '0') {
                    echo '<span class="enable">有効</span>';
                } else {
                    echo '<span class="disable">無効</span>';
                }
                echo '<br>';
            }
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- 条件式 -->
    <!-- 複数あり -->
    <td>
    <?php
        if (!empty($val2['trigger_info'])) {
            foreach ($val2['trigger_info'] as $val_triggers) {
                echo $val_triggers['expression'];
                echo '<br>';
            }
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- 深刻度 -->
    <!-- 複数あり -->
    <td>
    <?php
        if (!empty($val2['trigger_info'])) {
            foreach ($val2['trigger_info'] as $val_triggers) {
                switch ($val_triggers['priority']) {
                    case '0':
                        echo '<span class="not_classified">SNMPトラップ</span>' ;
                        break;
                    case '1':
                        echo '<span class="information">情報</span>' ;
                        break;
                    case '2':
                        echo '<span class="warning">警告</span>' ;
                        break;
                    case '3':
                        echo '<span class="average">軽度の障害</span>' ;
                        break;
                    case '4':
                        echo '<span class="high">重度の障害</span>' ;
                        break;
                    case '5':
                        echo '<span class="disaster">致命的な障害</span>' ;
                        break;
                }
                echo '<br>';
            }
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- ディスカバリID -->
    <td>
    <?php
        if (!empty($val2['discoveryrule_info']['0']['itemid'])) {
            echo $val2['discoveryrule_info']['0']['itemid']; 
        } else {
            echo '-';
        }
    ?>
    </td> 


    <!-- ディスカバリ名 -->
    <td>
    <?php 
        if (!empty($val2['discoveryrule_info']['0']['name'])) {
            echo $val2['discoveryrule_info']['0']['name'];
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- 有効/無効(ディスカバリ) -->
    <td>
    <?php
        if (!empty($val2['discoveryrule_info'])) {
            if ($val2['discoveryrule_info']['0']['status'] === '0') {
                echo '<span class="enable">有効</span>';
            } else {
                echo '<span class="disable">無効</span>';
            }
        } else {
            echo '-';
        }
    ?>
    </td>


    <!-- 更新間隔(ディスカバリ)[s] -->
    <td>
    <?php
        if (!empty($val2['discoveryrule_info']['0']['delay'])) {
            echo $val2['discoveryrule_info']['0']['delay']; 
        } else {
            echo '-';
        }
    ?>
    </td>

  </tr>

  <?php endforeach; ?>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p>No Items Found.</php>
<?php endif; ?>

