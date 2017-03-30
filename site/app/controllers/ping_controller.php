<?php

/**
 * PingController
 */
class PingController extends RestController
{

    public function index()
    {
        // APIを呼び出し
        $zabbixApi = $this->plugin->getZabbixApiInstance();

        // ホストグループ一覧
        $hostgroups = $zabbixApi->getHostgroup([
            'output' => [
                'groupid',
                'name',
            ],
            'filter' => [
                // flags
                // (readonly) Origin of the host group. 
                // Possible values: 
                // 0 - a plain host group; 
                // 4 - a discovered host group.
                'flags' => 0,
                // internal
                // (readonly) Whether the group is used internally by the system. An internal group cannot be deleted. 
                // Possible values: 
                // 0 - (default) not internal; 
                // 1 - internal.
                'internal' => 0,
            ],
        ]);
        $this->view->hostgroups = $hostgroups;

        // ページタイトル
        $this->view->setTitle('Ping');
    }

    public function post()
    {
        echo '<a href="/ping">back</a>';
        echo '<hr>';

//        var_dump($this->restParams);

        if (!isset($this->restParams['groupids'])) {
            echo 'グループIDがないよ';
            return;
        }

        if (!isset($this->restParams['status'])) {
            echo 'ステータスがないよ';
            return;
        }

        // モード
        // 0 - ping死活監視対象ホスト
        if (isset($this->restParams['mode']) && $this->restParams['mode'] === '0') {
            $mode = 'ping';
        } else {
            $mode = 'all';
        }

        // ステータス
        // 0 - すべてのホスト
        // 1 - 現在有効なホスト
        // 2 - 現在無効なホスト
        if ($this->restParams['status'] === '0') {
            $status = null;
        } elseif ($this->restParams['status'] === '1') {
            $status = 0;
        } elseif ($this->restParams['status'] === '2') {
            $status = 1;
        }

        // APIを呼び出し
        $zabbixApi = $this->plugin->getZabbixApiInstance();

        // 結果
        $results = [];

        // すべてのホストモード
        if ($mode === 'all') {
            // グループIDに紐づくホストを取得
            foreach ($this->restParams['groupids'] as $groupid) {
                $hosts[$groupid] = $zabbixApi->getHost([
                    'output' => [
                        'host',
                        'groupid',
                    ],
                    // host interface を取得
                    'selectInterfaces' => 'extend',
                    'filter' => [
                        // status が有効なものだけ
                        // 0 - (default) enabled item; 
                        // 1 - disabled item.
                        'status' => $status,
                    ],
                    'groupids' => $groupid,
                    'limit' => 0,
                ]);
            }

            foreach ($hosts as $groupid => $hostByGroups) {
                // ホストの有無をチェック
                if (empty($hostByGroups)) {
                    continue;
                }
                foreach ($hostByGroups as $hostByGroup) {
                    foreach ($hostByGroup['interfaces'] as $hostinterface) {
                        // $hostinterface['type']
                        // https://www.zabbix.com/documentation/2.2/manual/api/reference/hostinterface/object
                        //1 - agent
                        //2 - SNMP
                        //3 - IPMI
                        //4 - JMX
                        // TODO type指定すると SNMP か agent 決め打ちになってしまう
                        //                    if ($hostinterface['main'] === '1' && $hostinterface['type'] === '2') {
//                        if ($hostinterface['main'] === '1') {
                            $results[$groupid][] = [
                                'host' => $hostByGroup['host'],
                                'ip' => $hostinterface['ip'],
                            ];
//                        }
                    }
                }
            }
        }

        // ping死活監視対象モード
        if ($mode === 'ping') {
            // グループIDに紐づくping死活監視アイテムを取得
            foreach ($this->restParams['groupids'] as $groupid) {
                // アイテム
                $items = $zabbixApi->getItem([
                    'output' => [
                        'groupid',
                        'itemid',
                        'hostid',
                        'name',
                        'key_',
                        'status',
                        'interfaceid',
                    ],
                    'filter' => [
                        // status が有効なものだけ
                        // 0 - (default) enabled item; 
                        // 1 - disabled item.
//                        'status' => 0,
                    // トリガーが設定されているものだけ（icmppingだけに絞り込む）
                    // searchで icmpping[ 指定したので余計なフィルタ入れないほうが早いかも
                    //                    'with_triggers' => true,
                    ],
                    'search' => [
                        // キーが icmpping[* にマッチ
                        // filterだと完全一致なのでicmppingとしてしまうとicmppingloss/icmppingsecもヒットしてしまうのでとりあえずsearchで
                        // TODO Zabbix Agent で agent.ping を設定しているアイテムが取れない
                        'key_' => 'icmpping[',
                    //                    'key_' => 'icmpping',
                    ],
                    'limit' => 0,
                    'groupids' => $groupid,
                ]);
                // アイテムの有無をチェック
                if (empty($items)) {
                    continue;
                }

                foreach ($items as $item) {

                    // ホストインターフェース
                    $hostinterfaces = $zabbixApi->getHostinterface([
                        'output' => [
                            'hostid',
                            'type',
                            'ip',
                            'main',
                        ],
//                        'filter' => [
//                            // 標準インターフェイスのみ
//                            'main' => 1,
//                        ],
                        'interfaceids' => $item['interfaceid'],
                    ]);

                    // ホストインターフェースの有無をチェック
                    if (empty($hostinterfaces)) {
                        continue;
                    }

                    $hosts = $zabbixApi->getHost([
                        'output' => [
                            'host',
                        ],
                        'filter' => [
                            // status が有効なものだけ
                            // 0 - (default) enabled item; 
                            // 1 - disabled item.
                            'status' => $status,
                        ],
                        'hostids' => $item['hostid'],
                    ]);

                    // ホストの有無をチェック
                    if (empty($hosts)) {
                        continue;
                    }

                    $results[$groupid][] = [
                        'host' => reset($hosts)['host'],
                        'ip' => reset($hostinterfaces)['ip'],
                    ];
                }
            }
        }

//        echo '<pre>';
//        var_dump($results);
//        echo '</pre>';

        if (empty($results)) {
            'No CSV file created.';
        }

        // CSVファイル保存先
        $directory = PathManager::getHtdocsDirectory() . DIRECTORY_SEPARATOR . 'csv';
//        var_dump($directory);
        // CSVファイル保存
        foreach ($results as $hostgroupId => $result) {
            // ホストグループ
            $hostgroups = $zabbixApi->getHostgroup([
                'output' => [
                    'name',
                ],
                'groupids' => $hostgroupId,
            ]);
            // CSV操作
            $csvManipulator = new CsvManipulator();
            $csv = $csvManipulator->arrayToCsv($results[$hostgroupId]);

            file_put_contents($directory . DIRECTORY_SEPARATOR . reset($hostgroups)['name'] . '.csv', $csv);
        }

        // 保存済みCSV
        $files = $this->_search($directory);
//        var_dump($files);

        echo '<h2>CSVファイル（過去のも含む）</h2>';
        if (!empty($files)) {
            foreach ($files as $file) {
                echo '<a href="/csv/' . $file . '">' . $file . '</a>';
                echo '<br>';
            }
        } else {
            echo 'No CSV files.';
            echo '<br>';
        }

        echo '<h2>結果 dump</h2>';
        echo '<pre>';
        var_dump($results);
        echo '</pre>';
    }

    private function _search($directory)
    {
        // 保存済ファイルの検索
        $handle = opendir($directory);
        if ($handle) {
            $filename = null;
            // ディレクトリを捜査
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $filename[] = $file;
                }
            }
            closedir($handle);
        }
        return $filename;
    }
//    public function delete()
//    {
//        $result = array();
//        $result['result'] = false;
//        if (isset($this->post['filename'])) {
//            $result['filename'] = $this->post['filename'];
//            $result['result'] = unlink($result['filename']);
//        }
//        return $this->response->json($result);
//    }

}