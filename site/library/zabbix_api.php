<?php

/**
 * ZabbixApi
 *
 * @author Futoshi Gonokami <gonokami@allied-telesis.co.jp>
 */
class ZabbixApi
{
    /**
     * API エンドポイント
     */
    private $_endpoint = null;

    /**
     * API トークン
     */
    private $_token = null;

    /**
     * API ID
     */
    private $_id = null;

    /**
     * ヘッダ行となるキー
     */
    private $_headerRowKeys;

    /**
     * 配列
     */
    private $_array = [
        'header_rows' => null,
        'data_rows' => null,
        'structured' => null,
        'requests' => null,
    ];

    /**
     * 動的にZabbixApiメソッドを生成
     */
    public function __call($name, $args)
    {
        // メソッド名をZabbix API向けに組立
        // getHost => host.get
        $delimiter = '.';
        $_lowerName = strToLower(preg_replace('/([a-z])([A-Z])/', "$1$delimiter$2", $name));
        $_nameArray = explode($delimiter, $_lowerName);
        $method = $_nameArray[1] . '.' . $_nameArray[0];

        // TODO
        // $zabbixApi->getHost() のように引数が空でも呼び出せるようにしておく
        if (empty($args)) {
            $args[0] = [];
        }
        // 配列の 0 のみをパラメータとする
        $params = reset($args);
        return $this->call($method, $params);
    }

    /**
     * APIをコール
     */
    public function call($method, $params = null)
    {
        // debug
        $debug = false;
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            $debug = true;
        }

        // API ID を設定
        $this->_setId(time());

        // get だったらデフォルトパラメータを付与
        if (explode('.', $method)[1] === 'get') {
            // output 指定がなかったら extend にしておく
            if (!isset($params['output'])) {
                $params['output'] = 'extend';
            }

            // limit 指定がなかったら 100 にしておく
            if (!isset($params['limit'])) {
                $params['limit'] = 100;
            }
//            $debug = false;
        }

        // リクエストを設定
        $request['jsonrpc'] = '2.0';
        $request['method'] = $method;
        $request['params'] = $params;
        $request['auth'] = $this->getToken();
        $request['id'] = $this->_getId();

        if ($debug) {
            echo 'Request';
            var_dump($request);
            echo '<hr>';

            echo 'Token';
            var_dump($this->getToken());
            echo '<hr>';

            echo 'Endpoint';
            var_dump($this->getEndpoint());
            echo '<hr>';
        }

        try {
            // リクエストデータを JSON 形式に変換
            $tmpRequestJson = json_encode($request);
//            $requestJson = json_encode($request);
            // {#n} を削除
            $requestJson = (preg_replace('/{#\d+}/', '', $tmpRequestJson));

            // HTTP ストリームコンテキストの作成
            $options['http'] = array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json-rpc',
                'content' => $requestJson,
            );
            $context = stream_context_create($options);

            // リクエストの実行
            $responseJson = @file_get_contents($this->getEndpoint(), false, $context);

            if ($debug) {
                echo 'Request(JSON)';
                var_dump($requestJson);
                echo '<hr>';
                echo 'Response(JSON)';
                var_dump($responseJson);
                echo '<hr>';
            }

            if (!$responseJson) {
                throw new Exception('レスポンスを取得できませんでした。');
            }

            // レスポンス
            $response = json_decode($responseJson, true);

            if ($debug) {
                echo 'Response';
                var_dump($response);
                echo '<hr>';
            }

            // API エラーチェック
            if (isset($response['error'])) {
                // API エラーを設定
                throw new Exception($response['error']['message'] . ' - ' . $response['error']['data']);
            }

            // API ID チェック
            if (isset($response['id']) && $response['id'] !== $this->_getId()) {
                // API ID 不一致
                throw new Exception('API ID が違います。');
            }

            // ユーザ情報
            $session = new Session('user');
            // ログ
            Logger::setLogName('zabbix_api.log', 'zabbix_api');
            Logger::info(var_export([
                'user' => $session->user,
                'request' => $request,
                'response' => $response,
                            ], true), 'zabbix_api');

            // 結果 チェック
            return (isset($response['result'])) ? $response['result'] : null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function structurize($array, $resource, $method)
    {
        // 設定を読み込み
        $ini = Ini::load('zabbix.ini', $resource)[$method];

        // ヘッダ行とデータ行に分割
        $this->_splitIntoHeaderAndData($array);

        // データを構造化
        foreach ($this->_array['header_rows'] as $headerRowKey => $headerRowValues) {
            if ($headerRowKey === 0) {
                // ベースヘッダ
                $headerColumnCount = count($headerRowValues);
                $baseHeader = $headerRowValues;
            } else {
                // サブヘッダ + データ
                $j = 0;
                foreach ($this->_array['data_rows'] as $dataRowKey => $dataRowValues) {
                    for ($i = 0; $i < $headerColumnCount; $i++) {
                        // 1階層目のheader
//                        var_dump($baseHeader[$i]);
//                        var_dump($ini[$baseHeader[$i]]);
//                        echo '<hr>';
                        // 2階層目のheader
//                        var_dump($headerRowValues[$i]);
//                        var_dump($ini[$headerRowValues[$i]]);
//                        
                        // データ部分
//                        var_dump($dataRowValues[$i]);
//                        echo '<hr>';

                        /**
                         * 最初に不要なデータを消す
                         */
                        // 無効な要素を削除
                        if ($dataRowValues[$i] === '(null)') {
                            // データに (null) が指定されていたら null に置換え
//                            echo 'BASE HEADER';
//                            var_dump($baseHeader[$i]);
//                            echo 'HEADER ROW VALUES';
//                            var_dump($headerRowValues[$i]);
//                            echo 'DATA ROW VALUES';
//                            var_dump($dataRowValues[$i]);
//                            $baseHeader[$i] = null;
//                            $headerRowValues[$i] = null;
                            $dataRowValues[$i] = null;
                        }

                        /**
                         * 有効データのみ配列に格納
                         */
                        if ($dataRowValues[$i] !== null) {
                            // $dataRowValues が null のデータは配列に格納しない
                            /**
                             * 変換処理＆存在チェック
                             * TODO 機能を共通化する！
                             * for host.massadd
                             */
                            if ($resource === 'host' && $method === 'massadd') {

                                // interfaces の変更チェック
                                if ($baseHeader[$i] === 'interfaces' && $headerRowValues[$i] === 'type') {
                                    //
                                }

                                // hostid も templateid もこの処理なので暫定的にここで処理しちゃう
                                if ($baseHeader[$i] === 'hosts' && $headerRowValues[$i] === 'host') {
                                    // ホスト名からホストIDを取得
                                    $params[$i] = [
                                        'output' => [
                                            'hostid',
                                        ],
                                        'filter' => [
                                            'host' => $dataRowValues[$i],
                                        ],
                                    ];
                                    // 変換データ
                                    $_tmpResults[$i] = $this->call('host.get', $params[$i]);
                                    // 存在チェック
                                    if (empty($_tmpResults[$i])) {
                                        // 存在しないものだったら作成リストにメソッドを格納
                                        $createList[$j] = [
                                            'resource' => 'host',
                                            'method' => 'create',
                                            'params' => [
                                                [
                                                    'host' => $dataRowValues[$i]
                                                ],
                                            ],
                                        ];
                                    } else {
                                        // 存在するものだったら変換データを格納
                                        $convertList[$j]['hosts'] = [
                                            'hostid' => reset($_tmpResults[$i])['hostid'],
                                        ];
                                    }
                                } elseif ($baseHeader[$i] === 'templates' && $headerRowValues[$i] === 'name') {
                                    // テンプレート名からテンプレートIDを取得
                                    $params[$i] = [
                                        'output' => [
                                            'templateid',
                                        ],
                                        'filter' => [
                                            'name' => $dataRowValues[$i],
                                        ],
                                    ];
                                    // 変換データ
                                    $_tmpResults[$i] = $this->call('template.get', $params[$i]);
                                    // テンプレートは存在しない場合作成する処理入れてない（必要？）
//                                    $convertList[$j]['templates'] = [
//                                        [
//                                            'templateid' => reset($_tmpResults[$i])['templateid'],
//                                        ],
//                                    ];
                                    // 複数のテンプレート指定がある場合も名前→ID変換を有効に
                                    $convertList[$j]['templates'][] = [
                                        'templateid' => reset($_tmpResults[$i])['templateid'],
                                    ];
                                } elseif ($baseHeader[$i] === 'group' && $headerRowValues[$i] === 'name') {
                                    // グループ名からグループIDを取得
                                    $params[$i] = [
                                        'output' => [
                                            'groupid',
                                        ],
                                        'filter' => [
                                            'name' => $dataRowValues[$i],
                                        ],
                                    ];
                                    // 変換データ
                                    $_tmpResults[$i] = $this->call('hostgroup.get', $params[$i]);
                                    // ホストグループは存在しない場合作成する処理入れてない（必要？）
                                    $convertList[$j]['groups'] = [
                                        [
                                            'groupid' => reset($_tmpResults[$i])['groupid'],
                                        ],
                                    ];
                                } elseif ($baseHeader[$i] === 'proxy' && $headerRowValues[$i] === 'host') {
                                    // プロキシ名からプロキシIDを取得
                                    $params[$i] = [
                                        'output' => [
                                            'proxyid',
                                        ],
                                        'filter' => [
                                            'host' => $dataRowValues[$i],
                                        ],
                                    ];
                                    // 変換データ
                                    $_tmpResults[$i] = $this->call('proxy.get', $params[$i]);
                                    // プロキシは存在しない場合作成する処理入れてない（必要？）
                                    $convertList[$j]['proxy_hostid'] = reset($_tmpResults[$i])['proxyid'];
                                    
//                                    var_dump($convertList[$j]['proxy_hostid']);
//                                    die();
                                }
                            }
                            
                            /**
                             * 変換処理＆存在チェック
                             * for discoveryrule.create
                             */
                            // 暫定的に discoveryrule.create のみ変換処理を実行
                            if ($resource === 'discoveryrule' && $method === 'create') {
                                // interfacesidを調べる
                                if ($baseHeader[$i] === 'host' && $headerRowValues[$i] === 'name') {
                                    // ホスト名からホストIDを取得
                                    $params[$i] = [
                                        'output' => [
                                            'hostid',
                                        ],
                                        'filter' => [
                                            'host' => $dataRowValues[$i],
                                        ],
                                    ];
                                    // 変換データ
                                    $_tmpResults[$i] = $this->call('host.get', $params[$i]);
                                    // 存在チェック
                                    if (empty($_tmpResults[$i])) {
                                        // 存在しないものだったら作成リストにメソッドを格納
                                        $createList[$j] = [
                                            'resource' => 'host',
                                            'method' => 'create',
                                            'params' => [
                                                [
                                                    'host' => $dataRowValues[$i]
                                                ],
                                            ],
                                        ];
                                    } else {
                                        // 存在するものだったら変換データを格納
                                        $convertList[$j]['hostid'] = reset($_tmpResults[$i])['hostid'];
                                    }
                                } elseif ($baseHeader[$i] === 'interfaces') {
                                    // interface系のパラメータを格納
                                    $_tmpInterfaces[$j][$headerRowValues[$i]] = $dataRowValues[$i];
                                    $_tempInterfacesCount = count($_tmpInterfaces[$j]);
                                    if ($_tempInterfacesCount === 6) {
                                        // 6個分（type, main, useip, ip, dns, port）のデータがたまったら
                                        // 後でちゃんと直す
                                        // ホスト名からホストIDを取得
                                        $params[$i] = [
                                            'output' => [
                                                'interfaceid',
                                            ],
                                            'filter' => $_tmpInterfaces[$j],
                                        ];
                                        
                                        // 変換データ
                                        $_tmpResults[$i] = $this->call('hostinterface.get', $params[$i]);
//                                        var_dump($_tmpResults[$i]);
                                        $convertList[$j]['interfaceid'] = reset($_tmpResults[$i])['interfaceid'];
                                    }
                                    
                                }
                            }

                            /**
                             * 配列に格納
                             */
                            if ($headerRowValues[$i] !== '') {
                                // ヘッダ行の文字列が空だったら2行のヘッダとみなして配列に格納
                                // TODO 複数行ヘッダ対応
                                // iniファイルで？
                                if (isset($ini[$baseHeader[$i]]) && $ini[$baseHeader[$i]] === 'arraygroup') {
                                    // この形！
                                    //  'params' => 
                                    //    array (size=4)
                                    //      'host' => string 'TESTHOSTNAME1' (length=13)
                                    //      'groups' => 
                                    //        array (size=1)
                                    //          'groupid' => string '4' (length=1)
                                    //      'interfaces' => 
                                    //        array (size=2)
                                    //          0 => 
                                    //            array (size=6)
                                    //              'type' => string '1' (length=1)
                                    //              'main' => string '1' (length=1)
                                    //              'useip' => string '1' (length=1)
                                    //              'ip' => string '192.168.1.1' (length=11)
                                    //              'dns' => string '' (length=0)
                                    //              'port' => string '161' (length=3)
                                    //          1 => 
                                    //            array (size=6)
                                    //              'type' => string '1' (length=1)
                                    //              'main' => string '0' (length=1)
                                    //              'useip' => string '1' (length=1)
                                    //              'ip' => string '192.168.1.2' (length=11)
                                    //              'dns' => string '' (length=0)
                                    //              'port' => string '161' (length=3)
                                    //      'status' => string '0' (length=1) 
                                    // グループヘッダ
                                    $groupList[$j]['parent'] = $baseHeader[$i];
                                    // グループ内アイテム
                                    $groupList[$j]['children'] = array_keys($ini, 'groupby.' . $baseHeader[$i]);
                                    // グループ内アイテム数
                                    $groupItemCount = count($groupList[$j]['children']);
//                                    var_dump($groupItemCount);
                                    // arraygroup だったら [key => [[key => value]]] に
                                    // ひとつ前のループですでに格納されたグループ配列があるかチェックしたい
                                    // 格納
                                    $this->_array['structured'][$dataRowKey][$i] = [
                                        $baseHeader[$i] => [
                                            $headerRowValues[$i] => $dataRowValues[$i],
                                        ],
                                    ];

                                    // アイテム個数
//                                            var_dump($dataRowKey);
                                    // ベースヘッダ1行目
//                                            var_dump($baseHeader[$i]);
                                    // サブヘッダ2行目
//                                            var_dump($headerRowValues[$i]);
                                    // データ3行目以降
//                                            var_dump($dataRowValues[$i]);
                                } elseif (isset($ini[$baseHeader[$i]]) && $ini[$baseHeader[$i]] === 'array') {
                                    // array だったら [key => [[key => value]]] に
                                    $this->_array['structured'][$dataRowKey][$i] = [
                                        $baseHeader[$i] => [
                                            [
                                                $headerRowValues[$i] => $dataRowValues[$i],
                                            ],
                                        ],
                                    ];
                                } else {
                                    // array じゃなければ [key => [key => value]] に
                                    $this->_array['structured'][$dataRowKey][$i] = [
                                        $baseHeader[$i] => [
                                            $headerRowValues[$i] => $dataRowValues[$i],
                                        ],
                                    ];
                                }
                            } else {
                                // ヘッダ行の文字列があったら1行のヘッダとみなして文字列として格納
                                $this->_array['structured'][$dataRowKey][$i] = [
                                    $baseHeader[$i] => $dataRowValues[$i],
                                ];
                            }
                        }
                    }
                    $j++;
                }
            }
        }

//        var_dump($this->_array['structured']);
        // パラメータ配列
        $params = [];
        foreach ($this->_array['structured'] as $structuredRowKey => $structuredValues) {

//            var_dump($structuredRowKey);
//            var_dump($structuredValues);
            // グループリストがあったらまとめる
//            if (isset($groupList[$structuredRowKey])) {
//                var_dump($groupList[$structuredRowKey]);
//                var_dump($structuredValues);
//            }
            // 変換リストがあったら結合
            // 元のキーを削除する必要あり？？
            if (isset($convertList[$structuredRowKey])) {
                array_push($structuredValues, $convertList[$structuredRowKey]);
            }

            // 新規作成リストがあったら結合
            if (isset($createList[$structuredRowKey])) {
                $structuredValues = $createList[$structuredRowKey]['params'] + $structuredValues;
            }

            // リクエストを組み立てる
            foreach ($structuredValues as $structuredValue) {
                // 配列をマージ
                $params = array_merge_recursive($params, $structuredValue);
            }
            // リクエストデータを格納
            if (isset($createList[$structuredRowKey])) {
                // 新規作成だったら
                $this->_array['requests'][$structuredRowKey] = [
                    'resource' => $createList[$structuredRowKey]['resource'],
                    'method' => $createList[$structuredRowKey]['method'],
                    'params' => $params,
                ];
            } else {
                // 元のメソッドだったら
                $this->_array['requests'][$structuredRowKey] = [
                    'resource' => $resource,
                    'method' => $method,
                    'params' => $params,
                ];
            }
            // 一時リクエスト配列をクリア
            $params = [];
        }

//        var_dump($this->_array['requests']);
//        die();
        unset($array);
        return true;
    }

    /**
     * API エンドポイントを設定
     */
    public function setEndpoint($endpoint)
    {
        $this->_endpoint = $endpoint;
    }

    /**
     * API トークンを設定
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * API エンドポイントを取得
     */
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * API トークンを取得
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * API IDを取得
     */
    private function _getId()
    {
        return $this->_id;
    }

    /**
     * API IDを設定
     */
    private function _setId($data)
    {
        $this->_id = hash('sha256', $data);
    }

    /**
     * ヘッダ行キーを設定
     */
    public function setHeaderRowKeys($headerRowKeys)
    {
        if (is_array($headerRowKeys) && count($headerRowKeys) > 2) {
            throw new Exception('2行以上のヘッダは現在のところ許可されていません。');
        }
        $this->_headerRowKeys = $headerRowKeys;
    }

    /**
     * ヘッダ行とデータ行を分割
     */
    private function _splitIntoHeaderAndData($array)
    {
        foreach ($array as $key => $line) {
            if (is_int($this->_headerRowKeys) && $key === $this->_headerRowKeys) {
                // ヘッダ行指定が文字列の場合はキーと文字列を比較
                $this->_array['header_rows'][] = $line;
            } elseif (is_array($this->_headerRowKeys) && in_array($key, $this->_headerRowKeys)) {
                // ヘッダ行指定が配列の場合はキーと添字を比較
                $this->_array['header_rows'][] = $line;
            } else {
                // データ行
                $this->_array['data_rows'][] = $line;
            }
        }
    }

    /**
     * 配列を取得
     */
    public function getArray()
    {
        return $this->_array;
    }

    /**
     * ヘッダー行を取得
     */
    public function getHeaderRows()
    {
        return $this->_array['header_rows'];
    }

    /**
     * データ行を取得
     */
    public function getDataRows()
    {
        return $this->_array['data_rows'];
    }

    /**
     * 構造化データを取得
     */
    public function getStructured()
    {
        return $this->_array['data_rows'];
    }

    /**
     * リクエスト配列を取得
     */
    public function getRequests()
    {
        return $this->_array['requests'];
    }

}