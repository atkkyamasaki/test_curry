<?php

class DataCheckController extends RestController
{
    private $_resource;
    private $_method;
    private $_zabbixApi;

    public function index()
    {   

        // 事前定義
        // hostgroup 情報を API(get)  I
        $this->_resource = 'hostgroup';
        $this->_method = 'get';

        /*TODO：groupid 未選択はすべて表示するようにする*/
        // API で hostgroup 情報を収集
        if (!empty($this->query['groupids'])) {
            $zbx_hostgroups = $this->_zabbixApi->call($this->_resource . '.' . $this->_method, $this->query);

        } else {
            $zbx_hostgroups = null;
        }

        // API で host が登録されている hostgroup 情報を収集
        // (hostgroup 選択プルダウン用に取得)
        $zbx_hostgroups_in_host = $this->_zabbixApi->call($this->_resource . '.' . $this->_method, array('real_hosts' => '1'));

        // 取得した情報を VIEW へ渡す
        $this->view->zbx_hostgroups = $zbx_hostgroups;
        $this->view->zbx_hostgroups_in_host = $zbx_hostgroups_in_host;

    }

// ********************** 監視データ状況確認ツール ********************************************
    public function precheck()
    {

        /*TODO：GET 情報に groupid がない場合は Error になるような処理を追加する*/

        // GET 情報で取得した groupid から HostGroup 情報を API で収集
        $zbx_hostgroup = $this->_zabbixApi->call('hostgroup.get', $this->query);

        // GET 情報で取得した groupid に所属する Host を API で情報を収集
        $zbx_hosts = $this->_zabbixApi->call('host.get', $this->query);

        // 必要な情報のみを取得
        $new_hosts = array();
        foreach ($zbx_hosts as $val1) {

            // HostCheckCount の実行結果を取得
            $unix_time = time();
            $new_items = $this->_apiHostCheckCount($val1['hostid'], $unix_time);

            // 結果の配列から Type 毎の集計を実施
            $type = null;
            for ($i = 0; $i <= 17; $i++){
                if (!empty($new_items[$val1['hostid']][$i])) {
                    $types[$i] = array_count_values($new_items[$val1['hostid']][$i]);
                    $j = 0;
                    foreach ($types[$i] as $type_key => $type_value) {
                        if ($j === 0) {
                            $type[$i] = $this->_addDecisionTag($type_key) . ':' . $type_value;
                            $j = $j + 1;
                        } else {
                            $type[$i] .= '<br>' . $this->_addDecisionTag($type_key) . ':' . $type_value;
                        }
                    $j = null;
                    }
                } else {
                    $types[$i] = null;
                }
            }

            // 設定されていない監視 Type は NULL を追加しエラー対策
            for ($k = 0; $k <= 17; $k++) {
                if (empty($type[$k])) { $type[$k] = null; }
            }              

            // 各 type の取得状況結果の合計数を取得
            $total_array = null;
            foreach ($new_items[$val1['hostid']] as $totals) {
                foreach ($totals as $total) {
                    $total_array[] = $total;
                    $h = 0;
                    foreach (array_count_values($total_array) as $total_key => $total_value) {
                        if ($h === 0) {
                            $total_result = $this->_addDecisionTag($total_key) . ':' . $total_value;
                            $total_sum = $total_value;
                            $h = $h + 1;
                        } else {
                            $total_result .= '<br>' . $this->_addDecisionTag($total_key) . ':' . $total_value;
                            $total_sum = $total_sum + $total_value;
                        }
                    }
                }
            }

            // view へ渡す配列の生成
            $new_hosts[] = array(
                'hostid' => $val1['hostid'],
                'hostname' => $val1['host'],
                'status' => $val1['status'],
                'total' => $total_result . '<hr>Total：' .$total_sum,

                // 監視 type 一覧結果(現状不要なものを Filter) 
                '0' => $type[0],
                '1' => $type[1],
                '2' => $type[2],
                '3' => $type[3],
                '4' => $type[4],
                '5' => $type[5],
                '6' => $type[6],
                '7' => $type[7],
                '8' => $type[8],
                '9' => $type[9],
                '10' => $type[10],
                '11' => $type[11],
                '12' => $type[12],
                '13' => $type[13],
                '14' => $type[14],
                '15' => $type[15],
                '16' => $type[16],
                '17' => $type[17],
                );

        }

        // 取得した情報を VIEW へ渡す
        $this->view->new_hosts = $new_hosts;
        $this->view->query = $this->query;
        $this->view->zbx_hostgroup = $zbx_hostgroup;

    }

    private function _addDecisionTag($decision)
    {

        // *********** 判定結果に対して HTML タグ付けを行う ***********
        // (※ 共通化できなかったため datacheck_view.php にあるものと同じ Method を記載)

        switch ($decision) {
            case 'NG(Empty)':
                return '<span class="pink">NG(Empty)</span>' ;
                break;
            case 'Delay':
                return '<span class="red">Delay</span>' ;
                break;
            case 'OK':
                return '<span class="green">OK</span>' ;
                break;
            case 'Not Received':
                return '<span class="orange">Not Received</span>' ;
                break;
            case 'Received':
                return '<span class="green">Received</span>' ;
                break;
        }

    }


    public function hostcheck()
    {   
        /*TODO：GET 情報に hostid がない場合は Error になるような処理を追加する*/

        // 事前定義
        // host の Item 情報を API(get)  
        $this->_resource = 'item';
        $this->_method = 'get';
        
        // groupid から Host 情報を API(get) し、Host 名のみ取得
        /*TODO：Hostid を受け渡す方法を検討*/
        $zbx_hostname = $this->_zabbixApi->call('host.get', $this->query);
        foreach ($zbx_hostname as $val1) {
            foreach ($val1 as $key2 => $val2) {
                if ($key2 === 'name'){
                    $zbx_hostname = $val2;
                }
            }
        }

        // UNIXTIME の取得 (Item 最終取得時刻と現在時刻を比較するために取得)
        $unix_time = time();

        // Hostid,Itemid からデータの取得状況結果を取得
        if (isset($this->query['itemids'])) {
            $new_items = $this->_apiHostCheck($this->query['hostids'], $unix_time, $this->query['itemids']);
        } else {
            $new_items = $this->_apiHostCheck($this->query['hostids'], $unix_time);
        }

        // 取得した情報を VIEW へ渡す
        $this->view->zbx_hostname = $zbx_hostname;
        $this->view->new_items = $new_items;
        $this->view->unix_time = $unix_time;
        $this->view->query = $this->query;

    }

    private function _apiHostCheckCount($hostid, $unix_time, $itemid = NULL, $limit = 1000)
    {
        // Hostid から Host の Item 取得状況結果を返す
        // historymode=1(GET)であれば history デートを取得(低速)
        if (isset($this->query['historymode']) && $this->query['historymode'] === '1') {
            $zbx_items = $this->_apiHostCheckModuleDebug($hostid, $itemid, $limit);
        } else {
            $zbx_items = $this->_apiHostCheckModule($hostid, $itemid, $limit);
        }

        // 最新取得 Item 時刻が想定通りか計算処理
        $new_items = array(); 
        foreach ($zbx_items as $data2) { 
            // 'clock' キーがある場合は history 情報を格納、なければ item 情報を格納する
            if (isset($data2["clock"])) {
                $new_items[$hostid][$data2["type"]][] = $this->_typeJudge($data2["type"], $data2["clock"], $data2["delay"], $unix_time);
            } else {
                $new_items[$hostid][$data2["type"]][] = $this->_typeJudge($data2["type"], $data2["lastclock"], $data2["delay"], $unix_time);
            }
        }

        return $new_items;

    }

    private function _apiHostCheck($hostid, $unix_time, $itemid = NULL, $limit=1000)
    {
        // Hostid から Host の Item 取得状況結果を返す
        // historymode=1(GET)であれば history デートを取得(低速)
        if (isset($this->query['historymode']) && $this->query['historymode'] === '1') {
            $zbx_items = $this->_apiHostCheckModuleDebug($hostid, $itemid, $limit);
        } else {
            $zbx_items = $this->_apiHostCheckModule($hostid, $itemid, $limit);
        }

        // 最新取得 Item 時刻が想定通りか計算処理
        $new_items = array(); 
        foreach($zbx_items as $data2){
            // 'clock' キーがある場合は history 情報を格納、なければ item 情報を格納する
            if (isset($data2["clock"])) {
                $new_items[] = array(
                    'itemid' => $data2["itemid"],
                    'name' => $data2["name"],
                    'type' => $data2["type"],
                    'delay' => $data2["delay"],
                    'lastclock' => $data2["clock"],
                    'lastvalue' => $data2["value"],
                    'counttime' => $unix_time - $data2["clock"],
                    'decision' => $this->_typeJudge($data2["type"], $data2["clock"], $data2["delay"], $unix_time),
                    'error' => $data2["error"],
                    'hostid' => $hostid,
                );                 
            } else {
                $new_items[] = array(
                    'itemid' => $data2["itemid"],
                    'name' => $data2["name"],
                    'type' => $data2["type"],
                    'delay' => $data2["delay"],
                    'lastclock' => $data2["lastclock"],
                    'lastvalue' => $data2["lastvalue"],
                    'counttime' => $unix_time - $data2["lastclock"],
                    'decision' => $this->_typeJudge($data2["type"], $data2["lastclock"], $data2["delay"], $unix_time),
                    'error' => $data2["error"],
                    'hostid' => $hostid,
                ); 
            }
        }

        return $new_items;

    }

    private function _apiHostCheckModule($hostid, $itemid, $limit)
    {

        // API で item 情報を収集
        $zbx_items = $this->_zabbixApi->getItem([
            'webitems' => '1',
            /*TODO：必要なものだけ取得するように無駄を省く*/            
            // 'output' => [
            //     'hostid',
            //     'name',
            //     'key_',
            //     'delay',
            //     'lastclock',
            //     'lastvalue',
            // ],
            'filter' => [
                'hostid' => $hostid,
                'itemid' => $itemid,
                'status' => '0',
            ],
            'limit' => $limit,
        ]);

        return $zbx_items;
    }


    // ===================== itemid から history を取得し 'ZBX_HISTORY_PERIOD(24時間)' 以上前の情報も計算する場合のメソッド ======================
    // ※ 全 item 毎に history を取得するため低速 ※
    // (※ API の仕組み上、複数の itemid から一度に history を取得するのが難しい)
    private function _apiHostCheckModuleDebug($hostid, $itemid, $limit)
    {

        // API で item 情報を収集
        $zbx_items = $this->_zabbixApi->getItem([
            'webitems' => '1',
            /*TODO：必要なものだけ取得するように無駄を省く*/            
            'output' => [
                'itemid',
                'name',
                'type',
                'value_type',
                'delay',
                'error',
                'hostid',
            ],
            'filter' => [
                'hostid' => $hostid,
                'itemid' => $itemid,
                'status' => '0',
            ],
            'limit' => $limit,
        ]);

        // itemid の history を配列に追加する
        $i = 0;
        foreach ($zbx_items as $val1) {
            $zbx_historys = $this->_zabbixApi->getHistory([
                /*TODO：必要なものだけ取得するように無駄を省く*/  
                // 'output' => [
                //     'clock',
                //     'value',
                // ],
                'itemids' => $val1['itemid'],
                'history' => $val1['value_type'],
                'sortfield' => 'clock',
                'sortorder' => 'DESC',
                'limit' => '1',
            ]);

            // hisotry がない場合(データ未取得) "0" を入力
            if (empty($zbx_historys)) {
                $zbx_historys[] = array(
                    'itemid' => $val1['itemid'],
                    'clock' => '0',
                    'value' => '0',
                    'ns' => '0',
                );
            }

            // 各 itemid の配列の最後に history 情報を追加
            $zbx_items[$i] = array_merge($zbx_items[$i], array('clock'=>$zbx_historys['0']['clock'], 'value' => $zbx_historys['0']['value']));

            $zbx_historys = null;
            $i = $i + 1;

        }

        return $zbx_items;
    }


    private function _typeJudge($type, $lastclock, $delay, $unix_time)
    {
        // 監視タイプ毎の処理を決定する
        // 
        // [監視タイプ一覧]
        // 0 - Zabbix agent; 
        // 1 - SNMPv1 agent; 
        // 2 - Zabbix trapper; 
        // 3 - simple check; 
        // 4 - SNMPv2 agent; 
        // 5 - Zabbix internal; 
        // 6 - SNMPv3 agent; 
        // 7 - Zabbix agent (active); 
        // 8 - Zabbix aggregate; 
        // 9 - web item; 
        // 10 - external check; 
        // 11 - database monitor; 
        // 12 - IPMI agent; 
        // 13 - SSH agent; 
        // 14 - TELNET agent; 
        // 15 - calculated; 
        // 16 - JMX agent; 
        // 17 - SNMP trap;

        if ($type !== "2" and $type !== "7" and $type !== "17") {

            // 定期的にデータを取得するタイプの処理(0,1,3,4,5,6,8,9,10,11,12,13,14,15,16)
            return $this->_decisionCountTime($lastclock, $delay, $unix_time);  

        } else {

            // 定期的なデータ取得でないタイプの処理(2,7,17)
            return $this->_decisionDateExistence($lastclock, $unix_time);
        }
    }

    private function _decisionCountTime($lastclock, $delay, $unix_time)
    {
        /*TODO：経過時間の "大"、"小" で結果を分けれるようにしたい*/
        /*TODO：固定 60s については運用しつつ調整（Webから変更できるようにする）*/

        // 出力結果のまとめ
        //
        // 定期的にデータを取得するタイプの処理(0,1,3,4,5,6,8,9,10,11,12,13,14,15,16)
        //   データを一度も受信していない場合：NG(Empty)
        //   データを取得しているが遅延している：Delay
        //   データを取得していて遅延なし：OK
        //   
        // 定期的なデータ取得でないタイプの処理(2,7,17)
        //   データを一度も受信していない場合：Not Received
        //   データを取得している：Received


        // 最新 Item 情報が 取得間隔 +60S 以上経過していたら ”NG” と判定させる
        $delay = $delay + 60;

        if ($lastclock === "0") {
            return "NG(Empty)";
        } else {
            if ($delay > $unix_time - $lastclock) {
                return "OK";
            } else {
                return "Delay";
            }
        }

    }

    private function _decisionDateExistence($lastclock, $unix_time)
    {
        // 最新 Item 情報がなければ "Not Received"、受信されていれば "Received" と判定させる
        if ($lastclock === "0") {
            return "Not Received";
        } else {
            return "Received";
        }

    }


// ********************** 監視設定状況確認ツール ********************************************

    public function configcheck()
    {

        /*TODO：GET 情報に groupid がない場合は Error になるような処理を追加する*/

        // GET 情報で取得した groupid から HostGroup 情報を API で収集
        $zbx_hostgroup = $this->_zabbixApi->call('hostgroup.get', $this->query);

        // GET 情報で取得した groupid に所属する Host 情報を API で収集
        $zbx_hosts = $this->_zabbixApi->call('host.get', $this->query);

        foreach ($zbx_hosts as $val1) {
            // hostid 情報から item 情報を API で取得
            $new_items[] = $this->_zabbixApi->getItem([
                /*TODO：必要なものだけ取得するように無駄を省く*/            
                // 'output' => [
                //     'hostid',
                //     'itemid',
                //     'name',
                //     'interface',
                //     'key_',
                //     'interfaceid',
                //     'triggers',
                // ],
                'hostids' => $val1['hostid'],
                // 'status' => '0',
                // 'limit' => $limit,
                'webitems' => '1',
                'selectHosts' => '1',
                // 'selectInterfaces' => '1',
                'selectTriggers' => '1',
                'selectGraphs' => '1',
                'selectApplications' => '1',
                'selectDiscoveryRule' => '1',
                'selectItemDiscovery' => '1',
            ]);
        }

        // 各 id の配列を作成し、API で詳細を取得
        // Interface 詳細
        $new_hostinterfaces = $this->_apiInfoGet($new_items, 'getHostinterface', 'interfaceid', 'interfaceids');

        // Host 詳細
        $new_hosts = $this->_apiInfoGet($new_items, 'getHost', 'hostid', 'hostids');

        // Proxy 詳細
        $new_proxys = $this->_apiInfoGet($new_hosts, 'getProxy', 'proxy_hostid', 'proxyids');

        // Application 詳細
        $new_applications = $this->_apiInfoGet($new_items, 'getApplication', 'itemid', 'itemids');

        // Discovery rule 詳細
        $new_discoveryrules = $this->_apiInfoGet($new_items, 'getDiscoveryrule', 'itemid', 'itemids');

        // Trigger 詳細
        $new_triggers = $this->_apiInfoGet($new_items, 'getTrigger', 'itemid', 'itemids');

// echo '<pre>';
// var_dump($new_items);
// echo '</pre>';
// echo '<hr>';

        // 必要な情報を集めた配列を作成
        $monitor_lists = $new_items;

        $i = 0;
        $j = 0;
        foreach ($new_items as $val1) {
            foreach ($val1 as $val2) {

                // hostinterface 情報を配列に追加
                if ($val2['interfaceid'] !== '0' && $val2['interfaceid'] !== null) {
                    foreach ($new_hostinterfaces as $hostinterface_val1) {
                        foreach ($hostinterface_val1 as $hostinterface_val2) {
                            if ($val2['interfaceid'] === $hostinterface_val2['interfaceid']) {
                                $monitor_lists[$j][$i]['hostinterface_info'][] = $hostinterface_val2;
                            }
                        }
                    }
                }

                // host 情報を配列に追加
                if ($val2['hostid'] !== '0' && $val2['hostid'] !== null) {
                    foreach ($new_hosts as $hosts_val1) {
                        foreach ($hosts_val1 as $hosts_val2) {
                            if ($val2['hostid'] === $hosts_val2['hostid']) {
                                $monitor_lists[$j][$i]['host_info'][] = $hosts_val2;

                                // proxy 情報を配列に追加
                                if ($hosts_val2['proxy_hostid'] !== '0' && $hosts_val2['proxy_hostid'] !== null) {
                                    foreach ($new_proxys as $proxys_val1) {
                                        foreach ($proxys_val1 as $proxys_val2) {
                                            if ($hosts_val2['proxy_hostid'] === $proxys_val2['proxyid']) {
                                                $monitor_lists[$j][$i]['proxy_info'][] = $proxys_val2;
                                             }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Application 情報を配列に追加
                if ($val2['applications'] !== '0' && $val2['applications'] !== null) {
                    foreach ($val2['applications'] as $val3) {
                        foreach ($new_applications as $applications_val1) {
                            foreach ($applications_val1 as $applications_val2) {
                                if ($val3['applicationid'] === $applications_val2['applicationid']) {
                                    $monitor_lists[$j][$i]['application_info'][] = $applications_val2;
                                }
                            }
                        }
                    }
                }

                // Discovery rule 情報を配列に追加
                if ($val2['discoveryRule'] !== '0' && $val2['discoveryRule'] !== null) {
                    foreach ($val2['discoveryRule'] as $val3) {
                        foreach ($new_discoveryrules as $discoveryrules_val1) {
                            foreach ($discoveryrules_val1 as $discoveryrules_val2) {
                                if ($val3 === $discoveryrules_val2['itemid']) {
                                    $monitor_lists[$j][$i]['discoveryrule_info'][] = $discoveryrules_val2;
                                }
                            }
                        }
                    }
                }

                // trigger 情報を配列に追加
                if ($val2['triggers'] !== '0' && $val2['triggers'] !== null) {
                    foreach ($val2['triggers'] as $val3) {
                        foreach ($new_triggers as $triggers_val1) {
                            foreach ($triggers_val1 as $triggers_val2) {
                                if ($val3['triggerid'] === $triggers_val2['triggerid']) {
                                    $monitor_lists[$j][$i]['trigger_info'][] = $triggers_val2;
                                }
                            }
                        }
                    }
                }

                $i = $i + 1;
            }
            $i = 0;
            $j = $j + 1;
        }

        // 取得した情報を VIEW へ渡す
        $this->view->monitor_lists = $monitor_lists;
        $this->view->query = $this->query;
        $this->view->zbx_hostgroup = $zbx_hostgroup;


        // 取得した情報から CSV エクスポート用の配列を作成する

        $monitor_lists_new = $monitor_lists;
        $monitor_lists_csv['0'] = array('ホストID', 'ホスト名', '有効/無効(ホスト)', 'IP Address', 'Port番号', 'ProxyID', 'Proxy名', 'マクロ[Community名(SNMP)]', 'アプリケーションID', 'アプリケーション名', 'アイテムID', 'アイテム名', 'アイテムタイプ', '有効/無効(アイテム)', '更新間隔(アイテム)[s]', '保存時の計算', '乗数', '単位', 'ヒストリ保存期間[day]', 'トレンド保存期間[day]', 'データタイプ', 'トリガーID', 'トリガー名', '有効/無効(トリガー)', '条件式', '深刻度', 'ディスカバリID', 'ディスカバリ名', '有効/無効(ディスカバリ)', '更新間隔(ディスカバリ)[s]');

        $i = 1;
        foreach ($monitor_lists_new as $val1) {
            foreach ($val1 as $val2) {
                $j = 0;

                // ホストID
                $monitor_lists_csv[$i][$j] = $val2['hostid'];
                $j = $j + 1;

                // ホスト名
                $monitor_lists_csv[$i][$j] = $val2['host_info']['0']['host'];
                $j = $j + 1;

                // 有効/無効(ホスト)
                if ($val2['host_info']['0']['status'] === '0') {
                    $monitor_lists_csv[$i][$j] = '有効';
                } else {
                    $monitor_lists_csv[$i][$j] = '無効';
                }
                $j = $j + 1;

                // IP Address
                if (!empty($val2['hostinterface_info']['0']['ip'])) {
                    $monitor_lists_csv[$i][$j] = $val2['hostinterface_info']['0']['ip'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                } 
                $j = $j + 1;

                // Port番号
                if (!empty($val2['hostinterface_info']['0']['port'])) {
                    $monitor_lists_csv[$i][$j] = $val2['hostinterface_info']['0']['port'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                } 
                $j = $j + 1;

                // ProxyID
                if (!empty($val2['proxy_info']['0']['proxyid'])) {
                    $monitor_lists_csv[$i][$j] = $val2['proxy_info']['0']['proxyid'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                } 
                $j = $j + 1;

                // Proxy名
                if (!empty($val2['proxy_info']['0']['host'])) {
                    $monitor_lists_csv[$i][$j] = $val2['proxy_info']['0']['host'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                } 
                $j = $j + 1;

                // マクロ[Community名(SNMP)]
                if (!empty($val2['host_info']['0']['macros'])) {
                    foreach ($val2['host_info']['0']['macros'] as $val_macros) {
                        if (empty($monitor_lists_csv[$i][$j])) {
                            $monitor_lists_csv[$i][$j] = $val_macros['macro'] . ':' . $val_macros['value']; 
                        } else {
                            $monitor_lists_csv[$i][$j] .= ' & ' . $val_macros['macro'] . ':' . $val_macros['value']; 
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                } 
                $j = $j + 1;

                // アプリケーションID
                if (!empty($val2['application_info'])) {
                    foreach ($val2['application_info'] as $val_applications) {
                        if (empty($monitor_lists_csv[$i][$j])) {
                            $monitor_lists_csv[$i][$j] = $val_applications['applicationid'];
                        } else {
                            $monitor_lists_csv[$i][$j] .= ' & ' . $val_applications['applicationid'];
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                } 
                $j = $j + 1;

                // アプリケーション名
                if (!empty($val2['application_info'])) {
                    foreach ($val2['application_info'] as $val_applications) {
                         if (empty($monitor_lists_csv[$i][$j])) {
                            $monitor_lists_csv[$i][$j] = $val_applications['name'];
                        } else {
                            $monitor_lists_csv[$i][$j] .= ' & ' . $val_applications['name'];
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;


                // アイテムID
                $monitor_lists_csv[$i][$j] = $val2['itemid'];   
                $j = $j + 1;

                // アイテム名
                $monitor_lists_csv[$i][$j] = $val2['name'];     
                $j = $j + 1;

                // アイテムタイプ
                switch ($val2['type']) {
                    case '0':
                        $monitor_lists_csv[$i][$j] = 'Zabbix agent' ;
                        break;
                    case '1':
                        $monitor_lists_csv[$i][$j] = 'SNMPv1 agent' ;
                        break;
                    case '2':
                        $monitor_lists_csv[$i][$j] = 'Zabbix trapper' ;
                        break;
                    case '3':
                        $monitor_lists_csv[$i][$j] = 'simple check' ;
                        break;
                    case '4':
                        $monitor_lists_csv[$i][$j] = 'SNMPv2 agent' ;
                        break;
                    case '5':
                        $monitor_lists_csv[$i][$j] = 'Zabbix internal' ;
                        break;
                    case '6':
                        $monitor_lists_csv[$i][$j] = 'SNMPv3 agent' ;
                        break;
                    case '7':
                        $monitor_lists_csv[$i][$j] = 'Zabbix agent(active)' ;
                        break;
                    case '8':
                        $monitor_lists_csv[$i][$j] = 'Zabbix aggregate' ;
                        break;
                    case '9':
                        $monitor_lists_csv[$i][$j] = 'web item' ;
                        break;
                    case '10':
                        $monitor_lists_csv[$i][$j] = 'external check' ;
                        break;
                    case '11':
                        $monitor_lists_csv[$i][$j] = 'database monitor' ;
                        break;
                    case '12':
                        $monitor_lists_csv[$i][$j] = 'IPMI agent' ;
                        break;
                    case '13':
                        $monitor_lists_csv[$i][$j] = 'SSH agent' ;
                        break;
                    case '14':
                        $monitor_lists_csv[$i][$j] = 'TELNET agent' ;
                        break;
                    case '15':
                        $monitor_lists_csv[$i][$j] = 'calculated' ;
                        break;
                    case '16':
                        $monitor_lists_csv[$i][$j] = 'JMX agent' ;
                        break;
                    case '17':
                        $monitor_lists_csv[$i][$j] = 'SNMP trap' ;
                        break;
                }       
                $j = $j + 1;

                // 有効/無効(アイテム)
                if ($val2['status'] === '0') {
                    $monitor_lists_csv[$i][$j] = '有効';
                } else {
                    $monitor_lists_csv[$i][$j] = '無効';
                }       
                $j = $j + 1;

                // 更新間隔(アイテム)[s]
                $monitor_lists_csv[$i][$j] = $val2['delay'];    
                $j = $j + 1;

                // 保存時の計算 
                switch ($val2['delta']) {
                    case '0':
                        $monitor_lists_csv[$i][$j] = 'なし' ;
                        break;
                    case '1':
                        $monitor_lists_csv[$i][$j] = '差分/時間' ;
                        break;
                    case '2':
                        $monitor_lists_csv[$i][$j] = '差分' ;
                        break;
                }       
                $j = $j + 1;

                // 乗数
                if (!empty($val2['formula'])) {
                    $monitor_lists_csv[$i][$j] = $val2['formula'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // 単位
                if (!empty($val2['units'])) {
                    $monitor_lists_csv[$i][$j] = $val2['units'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // ヒストリ保存期間[day] 
                $monitor_lists_csv[$i][$j] = $val2['history'];      
                $j = $j + 1;

                // トレンド保存期間[day] 
                $monitor_lists_csv[$i][$j] = $val2['trends'];   
                $j = $j + 1;

                // データタイプ
                switch ($val2['value_type']) {
                    case '0':
                        $monitor_lists_csv[$i][$j] = '数値(浮動少数)' ;
                        break;
                    case '1':
                        $monitor_lists_csv[$i][$j] = '文字列' ;
                        break;
                    case '2':
                        $monitor_lists_csv[$i][$j] = 'ログ' ;
                        break;
                    case '3':
                        $monitor_lists_csv[$i][$j] = '数値(整数)' ;
                        break;
                    case '4':
                        $monitor_lists_csv[$i][$j] = 'テキスト' ;
                        break;
                }       
                $j = $j + 1;

                // トリガーID 
                if (!empty($val2['trigger_info'])) {
                    foreach ($val2['trigger_info'] as $val_triggers) {
                         if (empty($monitor_lists_csv[$i][$j])) {
                            $monitor_lists_csv[$i][$j] = $val_triggers['triggerid'];
                        } else {
                            $monitor_lists_csv[$i][$j] .= ' & ' . $val_triggers['triggerid'];
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // トリガー名
                if (!empty($val2['trigger_info'])) {
                    foreach ($val2['trigger_info'] as $val_triggers) {
                         if (empty($monitor_lists_csv[$i][$j])) {
                            $monitor_lists_csv[$i][$j] = $val_triggers['description'];
                        } else {
                            $monitor_lists_csv[$i][$j] .= ' & ' . $val_triggers['description'];
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // 有効/無効(トリガー)
                if (!empty($val2['trigger_info'])) {
                    foreach ($val2['trigger_info'] as $val_triggers) {
                         if (empty($monitor_lists_csv[$i][$j])) {
                            if ($val_triggers['status'] === '0') {
                                $monitor_lists_csv[$i][$j] = '有効';
                            } else {
                                $monitor_lists_csv[$i][$j] = '無効';
                            }
                        } else {
                            if ($val_triggers['status'] === '0') {
                                $monitor_lists_csv[$i][$j] .= ' & ' . '有効';
                            } else {
                                $monitor_lists_csv[$i][$j] .= ' & ' . '無効';
                            }
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // 条件式
                if (!empty($val2['trigger_info'])) {
                    foreach ($val2['trigger_info'] as $val_triggers) {
                         if (empty($monitor_lists_csv[$i][$j])) {
                            $monitor_lists_csv[$i][$j] = $val_triggers['expression'];
                        } else {
                            $monitor_lists_csv[$i][$j] .= ' & ' . $val_triggers['expression'];
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // 深刻度
                if (!empty($val2['trigger_info'])) {
                    foreach ($val2['trigger_info'] as $val_triggers) {
                         if (empty($monitor_lists_csv[$i][$j])) {
                            switch ($val_triggers['priority']) {
                                case '0':
                                    $monitor_lists_csv[$i][$j] = 'SNMPトラップ' ;
                                    break;
                                case '1':
                                    $monitor_lists_csv[$i][$j] = '情報' ;
                                    break;
                                case '2':
                                    $monitor_lists_csv[$i][$j] = '警告' ;
                                    break;
                                case '3':
                                    $monitor_lists_csv[$i][$j] = '軽度の障害' ;
                                    break;
                                case '4':
                                    $monitor_lists_csv[$i][$j] = '重度の障害' ;
                                    break;
                                case '5':
                                    $monitor_lists_csv[$i][$j] = '致命的な障害' ;
                                    break;
                            }
                        } else {
                            switch ($val_triggers['priority']) {
                                case '0':
                                    $monitor_lists_csv[$i][$j] .= ' & ' . 'SNMPトラップ' ;
                                    break;
                                case '1':
                                    $monitor_lists_csv[$i][$j] .= ' & ' . '情報' ;
                                    break;
                                case '2':
                                    $monitor_lists_csv[$i][$j] .= ' & ' . '警告' ;
                                    break;
                                case '3':
                                    $monitor_lists_csv[$i][$j] .= ' & ' . '軽度の障害' ;
                                    break;
                                case '4':
                                    $monitor_lists_csv[$i][$j] .= ' & ' . '重度の障害' ;
                                    break;
                                case '5':
                                    $monitor_lists_csv[$i][$j] .= ' & ' . '致命的な障害' ;
                                    break;
                            }
                        }
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }   
                $j = $j + 1;
            

                // ディスカバリID
                if (!empty($val2['discoveryrule_info']['0']['itemid'])) {
                    $monitor_lists_csv[$i][$j] = $val2['discoveryrule_info']['0']['itemid']; 
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // ディスカバリ名
                if (!empty($val2['discoveryrule_info']['0']['name'])) {
                    $monitor_lists_csv[$i][$j] = $val2['discoveryrule_info']['0']['name'];
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }       
                $j = $j + 1;

                // 有効/無効(ディスカバリ)
                if (!empty($val2['discoveryrule_info'])) {
                    if ($val2['discoveryrule_info']['0']['status'] === '0') {
                        $monitor_lists_csv[$i][$j] = '有効';
                    } else {
                        $monitor_lists_csv[$i][$j] = '無効';
                    }
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }   
                $j = $j + 1;
            
                // 更新間隔(ディスカバリ)[s]
                if (!empty($val2['discoveryrule_info']['0']['delay'])) {
                    $monitor_lists_csv[$i][$j] = $val2['discoveryrule_info']['0']['delay']; 
                } else {
                    $monitor_lists_csv[$i][$j] = '-';
                }
                $j = $j + 1;

            $i = $i + 1;
            }
        }


        // CSV 保存先ディレクトリ
        $directory = PathManager::getHtdocsDirectory() . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR;

        // ファイル名（保存先ディレクトリ + ファイル名）
        $filename = $directory . "config.csv";

        // CSVファイルに書き込み
        $this->file_put_csv($filename, $monitor_lists_csv);

        // 取得した情報を VIEW へ渡す
        $this->view->filename = $filename;


    }

    function file_put_csv( $filename,$lines, $file_mode="w", $delim=",",  $enclosure="\"" )
    {
        // 2次元配列から CSV 形式でファイルに書き込む Method

        $fp = fopen( $filename, $file_mode );
        fwrite($fp, "\xEF\xBB\xBF");
        $state =null;
        foreach( $lines as $idx => $line ){
            $state = fputcsv( $fp, $line, $delim, $enclosure );
        }
        return $state;
    }

    private function _apiInfoGet($item_array, $api_get, $array_key, $api_param)
    {
        // 各情報を API GET して配列に格納する Method

        switch ($api_get) {

            case 'getDiscoveryrule':
                foreach ($item_array as $val1) {
                    foreach ($val1 as $val2) {
                        if (!empty($val2['discoveryRule'])) {
                            foreach ($val2['discoveryRule'] as $val3) {
                                $list_array[] = $val3;
                            }
                        }
                    }
                }
                break;

            default:
                foreach ($item_array as $val1) {
                    foreach ($val1 as $val2) {
                        if (!empty($val2[$array_key])) {
                            $list_array[] = $val2[$array_key];
                        }
                    }
                }
                break;
        }

        if (!empty($list_array)) {
            switch ($api_get) { 

                case 'getTrigger':
                    $new_array[] = $this->_zabbixApi->$api_get([
                        // 'output' => [
                        //     'triggerid',
                        //     'description ',
                        //     'expression  ',
                        //     'priority ',
                        //     'type ',
                        //     'status ',
                        //     'state ',
                        //     'flags ',
                        //     'error ',
                        // ],
                        $api_param => $list_array,
                        'expandExpression' => '1',      // 条件式も出力する場合(Default:表示されない)
                        'expandComment' => '1',     // マクロ利用時にマクロを展開する(Default:マクロ展開されない)
                        'expandDescription' => '1',     // マクロ利用時にマクロを展開する(Default:マクロ展開されない)
                    ]);
                    break;  

                case 'getApplication':
                    $new_array[] = $this->_zabbixApi->$api_get([
                        'output' => [
                            'applicationid',
                            'name',
                        ],
                        $api_param => $list_array,
                    ]);
                    break;  

                case 'getDiscoveryrule':
                    $new_array[] = $this->_zabbixApi->$api_get([
                        'output' => [
                            'itemid',
                            'delay',
                            'key_ ',
                            'name',
                            'type ',
                            'state',
                            'status',
                            'error',
                        ],
                        $api_param => $list_array,
                    ]);  
                    break;  

                case 'getHost':
                    $new_array[] = $this->_zabbixApi->$api_get([
                        $api_param => $list_array,
                        'selectMacros' => 'extend',
                    ]);  
                    break;  

                case 'getProxy':
                    $new_array[] = $this->_zabbixApi->$api_get([
                        'output' => [
                            'proxyid',
                            'host',
                            'status',
                        ],
                        $api_param => $list_array,
                    ]);  
                    break;  

                default:
                    $new_array[] = $this->_zabbixApi->$api_get([
                        $api_param => $list_array,
                    ]);  
                    break;  

            }   
            return $new_array;
        }
    }


    public function preProcess()
    {
        // ZabbixAPI を利用するために preProcess で呼び出し
        $this->_zabbixApi = $this->plugin->getZabbixApiInstance();
    }

    public function postProcess()
    {

    }






}
