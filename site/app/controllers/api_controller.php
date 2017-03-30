<?php

class ApiController extends RestController
{
    private $_resource;
    private $_method;
    private $_zabbixApi;

    /**
     * 表示
     * TODO Ajax通信時の処理を共通化したい
     */
    public function index()
    {
        // Ajax通信
        if ($this->request->isXmlHttp()) {
            
//            var_dump($this->restParams);
//            var_dump($this->_resource);
//            var_dump($this->_method);
//            return;
            
            // シングルトンだったらログイン中とは別のZabbixAPIインスタンスを生成して使う
            if (isset($this->restParams['is_singleton']) && $this->restParams['is_singleton'] === 'false') {
                // 新規ZabbixAPIインスタンスを生成
                $zabbixApi = $this->plugin->getZabbixApiInstance(false);
                // エンドポイントをセット
                $zabbixApi->setEndpoint($this->restParams['endpoint']);
                // トークンをセット
                $zabbixApi->setToken($this->restParams['token']);
                // ZabbixAPIへリクエスト
                $responses = $zabbixApi->call($this->_resource . '.' . $this->_method, isset($this->restParams['params']) ? $this->restParams['params'] : null);
                // レスポンスをJSONで返す
                $this->response->json($responses);
                return;
            }
            
            // エンドポイントとトークンがなければすでにあるインスタンスを使う
            $responses = $this->_zabbixApi->call($this->_resource . '.' . $this->_method, isset($this->restParams['params']) ? $this->restParams['params'] : null);
            $this->response->json($responses);
            return;
        }
        
        // 通常のhttp通信
        $responses = $this->_zabbixApi->call($this->_resource . '.' . $this->_method, $this->query);
        $this->view->responses = $responses;
    }

    /**
     * 作成
     */
    public function post()
    {
        if (isset($_FILES['csv'])) {
            $this->_parseCsv($_FILES['csv']);
        } else {
            // CSVまたはクエリからリクエストパラメータを生成する
            try {
                if (isset($this->restParams['csv'])) {
                    // CSV操作
                    $csvManipulator = new CsvManipulator();
                    $csvArray = $csvManipulator->csvToArray($this->restParams['csv']);

                    // リクエスト組み立て
                    $this->_zabbixApi->setHeaderRowKeys([0, 1]);
                    $this->_zabbixApi->structurize($csvArray, $this->_resource, $this->_method);
                    $requests = $this->_zabbixApi->getRequests();
                } else {
                    $requests = [
                        'resource' => $this->_resource,
                        'method' => $this->_method,
                        'params' => $this->query,
                    ];
                }

                // max_execution_time
                // スクリプトがパーサにより強制終了されるまでに許容される最大の 時間を秒単位で指定します。この命令は、いい加減に書かれた スクリプトがサーバーの負荷を上げることを防止するのに役立ちます。 デフォルトでは、30 に設定されています。 PHP を コマンドライン から実行する場合のデフォルト設定は 0 です。
                ini_set('max_execution_time', 0);

                // set_time_limit
                // スクリプトが実行可能な秒数を設定します。 この制限にかかるとスクリプトは致命的エラーを返します。 デフォルトの制限値は 30 秒です。 なお、php.iniでmax_execution_timeの 値が定義されている場合にはそれを用います。
                // set_time_limit(0)

                $responses = [];
                foreach ($requests as $request) {
                    $responses[] = $this->_zabbixApi->call($request['resource'] . '.' . $request['method'], $request['params']);
                }
                
                /**
                 * ディスカバリルール作成の場合
                 * リンクトラップの有効無効を指定して作成
                 */
                if ($request['resource'] === 'discoveryrule' && $request['method'] === 'create') {
                    $this->_linktrap($requests, $responses);
                }
                
                // レンダリングを有効に
                // 有効にしないとViewScriptに処理が行かない
                $this->view->enableRendering(true);
                $this->view->responses = $responses;
            } catch (Exception $e) {
                $this->view->message = $e->getMessage();
            }
        }
    }

    /**
     * 更新
     */
    public function put()
    {
        if (isset($_FILES['csv'])) {
            $this->_parseCsv($_FILES['csv']);
        } else {
            // CSVまたはクエリからリクエストパラメータを生成する
            try {
                if (isset($this->restParams['csv'])) {
                    // CSV操作
                    $csvManipulator = new CsvManipulator();
                    $csvArray = $csvManipulator->csvToArray($this->restParams['csv']);

                    // リクエスト組み立て
                    $this->_zabbixApi->setHeaderRowKeys([0, 1]);
                    $this->_zabbixApi->structurize($csvArray, $this->_resource, $this->_method);
                    $requests = $this->_zabbixApi->getRequests();
                } else {
                    $requests = [
                        'resource' => $this->_resource,
                        'method' => $this->_method,
                        'params' => $this->query,
                    ];
                }

                // max_execution_time
                // スクリプトがパーサにより強制終了されるまでに許容される最大の 時間を秒単位で指定します。この命令は、いい加減に書かれた スクリプトがサーバーの負荷を上げることを防止するのに役立ちます。 デフォルトでは、30 に設定されています。 PHP を コマンドライン から実行する場合のデフォルト設定は 0 です。
                ini_set('max_execution_time', 0);

                // set_time_limit
                // スクリプトが実行可能な秒数を設定します。 この制限にかかるとスクリプトは致命的エラーを返します。 デフォルトの制限値は 30 秒です。 なお、php.iniでmax_execution_timeの 値が定義されている場合にはそれを用います。
                // set_time_limit(0)

                $responses = [];
                foreach ($requests as $request) {
                    $responses[] = $this->_zabbixApi->call($request['resource'] . '.' . $request['method'], $request['params']);
                }                
                // レンダリングを有効に
                // 有効にしないとViewScriptに処理が行かない
                $this->view->enableRendering(true);
                $this->view->responses = $responses;
            } catch (Exception $e) {
                $this->view->message = $e->getMessage();
            }
        }
    }

    /**
     * 削除
     */
    public function delete()
    {
        echo 'DELETEアクセス';
    }

    /**
     * CSVファイルをパース
     */
    private function _parseCsv($csv)
    {
        /**
         * STEP1 CSVファイルがアップロードされたらファイル内容を確認させる
         * CSVファイルをチェックしてテーブルにして返す
         */
        try {
            // エラーチェック
            if (!isset($_FILES['csv']['tmp_name']) && !isset($_FILES['csv']['error']) && !is_int($_FILES['csv']['error'])) {
                throw new Exception('不明なファイルエラー。');
            }

            // CSV操作
            $csvManipulator = new CsvManipulator();
            $csvManipulator->validate($_FILES['csv']['error']);
            // CSVファイルをデータディレクトリに移動
            $csvFilename = PathManager::getDataDirectory() . $_FILES['csv']['tmp_name'];
            move_uploaded_file($_FILES['csv']['tmp_name'], $csvFilename);
            $this->view->csv = $csvFilename;
            $csvArray = $csvManipulator->csvToArray($csvFilename);

            // リクエスト組み立て
            $this->_zabbixApi->setHeaderRowKeys([0, 1]);
            $this->_zabbixApi->structurize($csvArray, $this->_resource, $this->_method);

            $this->view->header_rows = $this->_zabbixApi->getHeaderRows();
            $this->view->data_rows = $this->_zabbixApi->getDataRows();

            // レンダリングを有効に
            // 有効にしないとViewScriptに処理が行かない
            $this->view->enableRendering(true);
        } catch (Exception $e) {
            $this->view->message = $e->getMessage();
        }
    }
    
    /*
     * LinkTrap の有効無効を切り替えるでディスカバリルールを作成
     * discoveryrule.create
     */
    private function _linktrap($requests, $responses)
    {
//        echo 'discoveryrule.create results:';
//        var_dump($responses);
//            $responses = 
//                array (
//                  0 => 
//                  array (
//                    'itemids' => 
//                    array (
//                      0 => '27091',
//                    ),
//                  ),
//                  1 => 
//                  array (
//                    'itemids' => 
//                    array (
//                      0 => '27092',
//                    ),
//                  ),
//                );

//        var_dump($requests);
//        var_dump($responses);

        /**
         * itemprototype.create
         */
        $processCount = 0;
        foreach ($requests as $request) {
//            // request
//            var_dump($request);
//            // hostid
//            var_dump($request['params']['hostid']);
//            // hostid
//            var_dump($request['params']['interfaceid']);
//            // itemid
//            var_dump(reset($responses[$processCount]['itemids']));
//            // itemprototype key_
//            var_dump($request['params']['key_']);
//            // hostname
//            var_dump($request['params']['host']['name']);
//            // delay
//            var_dump($request['params']['delay']);

            // アプリケーション存在確認
            // application.exists
            $applicationExistsResponse[$processCount] = $this->_zabbixApi->call(
                'application.exists',
                [
                    'hostid' => $request['params']['hostid'],
                    'name' => '9999.SNMPトラップ',
                ]
            );
            if ($applicationExistsResponse[$processCount] === false) {
                // アプリケーションがなかったら作成
                // application.create
                $applicationCreateResponses[$processCount] = $this->_zabbixApi->call(
                    'application.create',
                    [
                        'hostid' => $request['params']['hostid'],
                        'name' => '9999.SNMPトラップ',
                    ]
                );
//                array (size=1)
//                  'applicationids' => 
//                    array (size=1)
//                      0 => string '1108' (length=4)
                    // applicationid を格納
                $applicationIds[$processCount] = reset($applicationCreateResponses[$processCount]['applicationids']);
            } elseif ($applicationExistsResponse[$processCount] === true) {
                // アプリケーションがあったらapplicationidを取得
                // application.get
                $applicationGetResponses[$processCount] = $this->_zabbixApi->call(
                    'application.get',
                    [
                        'hostids' => $request['params']['hostid'],
                        'output' => [
                            'applicationid',
                        ],
                        'filter' => [
                            'name' => '9999.SNMPトラップ',
                        ],
                    ]
                );
//                echo '$applicationGetResponses(applicationid)';
//                array (size=1)
//                  0 => 
//                    array (size=1)
//                      'applicationid' => string '1107' (length=4)
                // applicationid を格納
                $applicationIds[$processCount] = reset($applicationGetResponses[$processCount])['applicationid'];
            }

            // key_ で分岐
            if ($request['params']['key_'] === 'if-mib_linktrap_enatrriger_discovery') {
                // temprototype.create
                $itemprototypeCreateResponses[] = $this->_zabbixApi->call(
                    'itemprototype.create',
                    [
                        'delay' => $request['params']['delay'],
                        'hostid' => $request['params']['hostid'],
                        'interfaceid' => $request['params']['interfaceid'],
                        'name' => 'linkdown,linkup - {#SNMPVALUE}',
                        'type' => '17',
                        'ruleid' => reset($responses[$processCount]['itemids']),
                        'key_' => 'snmptrap["LINKTRAP,IDX={#SNMPINDEX}#"]',
                        'value_type' => '2',
                        'history' => '365',
                        'applications' => [$applicationIds[$processCount]],
                    ]
                );
                // triggerprototype.create
                // key_ が if-mib_linktrap_enatrriger_discovery の場合はトリガーのプロトタイプを作成
                // リンクダウン
                $triggerprototypeCreateResponses[] = $this->_zabbixApi->call(
                    'triggerprototype.create',
                    [
                        'description' => '[SNMPトラップ:linkDown] {#SNMPVALUE}がリンクダウンしました。',
                        'expression' => '{' . $request['params']['host']['name'] . ':snmptrap["LINKTRAP,IDX={#SNMPINDEX}#"].nodata({$TRIGGER_TIME})}=0&{' . $request['params']['host']['name'] . ':snmptrap["LINKTRAP,IDX={#SNMPINDEX}#"].str("status_code:0")}=1',
                        'priority' => '0',
                    ]
                );
                // リンクアップ
                $triggerprototypeCreateResponses[] = $this->_zabbixApi->call(
                    'triggerprototype.create',
                    [
                        'description' => ' [SNMPトラップ:linkUp] {#SNMPVALUE}がリンクアップしました。',
                        'expression' => '{' . $request['params']['host']['name'] . ':snmptrap["LINKTRAP,IDX={#SNMPINDEX}#"].nodata({$TRIGGER_TIME})}=0&{' . $request['params']['host']['name'] . ':snmptrap["LINKTRAP,IDX={#SNMPINDEX}#"].str("status_code:1")}=1',
                        'priority' => '0',
                    ]
                );
            } elseif ($request['params']['key_'] === 'if-mib_linktrap_distrriger_discovery') {
                // key_ が if-mib_linktrap_enatrriger_discovery の場合はアイテムプロトタイプのキーにスペースを入れる（重複防止の為）
                // temprototype.create
                $itemprototypeCreateResponses[] = $this->_zabbixApi->call(
                    'itemprototype.create',
                    [
                        'delay' => $request['params']['delay'],
                        'hostid' => $request['params']['hostid'],
                        'interfaceid' => $request['params']['interfaceid'],
                        'name' => 'linkdown,linkup - {#SNMPVALUE}',
                        'type' => '17',
                        'ruleid' => reset($responses[$processCount]['itemids']),
                        'key_' => 'snmptrap["LINKTRAP,IDX={#SNMPINDEX}#" ]', // スペース空けて重複回避
                        'value_type' => '2',
                        'history' => '365',
                        'applications' => [$applicationIds[$processCount]],
                    ]
                );
            }

            // 結果
            if (isset($applicationCreateResponses)) {
                echo 'application.create results:';
                var_dump($applicationCreateResponses);
            }
            echo 'itemprototype.create results:';
            var_dump($itemprototypeCreateResponses);
            echo 'triggerprototype.create results:';
            var_dump($triggerprototypeCreateResponses);
            $processCount++;
        }
    }

    /**
     * プリプロセス
     */
    public function preProcess()
    {
        // リソース
        $this->_resource = $this->params['resource'];
        // メソッド
        $this->_method = $this->_getMethod($this->request->getMethod());
        // APIを呼び出し
        $this->_zabbixApi = $this->plugin->getZabbixApiInstance();
    }

    /**
     * メソッドを取得
     */
    private function _getMethod($httpMethod)
    {
        if ($httpMethod === 'GET') {
            return 'get';
        }
        if ($httpMethod === 'POST') {
            return 'create';
        }
        if ($httpMethod === 'PUT') {
            return 'massadd';
//            return 'massupdate';
        }
        if ($httpMethod === 'DELETE') {
            return 'delete';
        }
    }

    /**
     * ポストプロセス
     */
    public function postProcess()
    {
        // ページタイトル
        $this->view->setTitle(ucfirst($this->_resource) . ' ' . $this->_method);
    }

}