<?php

class ExchangeController extends RestController
{
    // デバッグフラグ
    private $debug = true;
    // フォーマット json or xml
    private $_format = 'json';
    // テンプレート名に含まれる置換対象語
    private $_searchTemplateWord = '';
    // テンプレート名の置換語
    private $_replaceTemplateWord = '';
    // ソースのホストグループ名
    private $_sourceHostgroupName = '';
    // ディスティネーションのホストグループ名
    private $_destinationHostgroupName = '';
    // ソースのZabbixApiインスタンス
    private $_sourceZabbixApi = null;
    // ディスティネーションのZabbixApiインスタンス
    private $_destinationZabbixApi = null;
    // インポートルール
    // Zabbix管理画面ではデフォルトで以下が無効
    // hosts, images, maps, screens
    private $_importRules = [
        'applications' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
        'discoveryRules' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
        'graphs' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
        'groups' => [
            'createMissing' => true,
        ],
//        'hosts' => [
//            'createMissing' => true,
//            'updateExisting' => true,
//        ],
//        'images' => [
//            'createMissing' => true,
//            'updateExisting' => true,
//        ],
        'items' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
//        'maps' => [
//            'createMissing' => true,
//            'updateExisting' => true,
//        ],
//        'screens' => [
//            'createMissing' => true,
//            'updateExisting' => true,
//        ],
        'templateLinkage' => [
            'createMissing' => true,
        ],
        'templates' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
        'templateScreens' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
        'triggers' => [
            'createMissing' => true,
            'updateExisting' => true,
        ],
    ];
    public function index()
    {
        // ページタイトル
        $this->view->setTitle('Exchange');

        // エンドポイント一覧を取得
        $endpoints = Ini::load('config.ini', 'endpoints');
        $this->view->endpoints = $endpoints;

        // ホストグループ一覧を取得
        $zabbixApi = $this->plugin->getZabbixApiInstance();
        $this->view->hostgroups = $zabbixApi->getHostgroup();
    }
    /**
     * TODO 現在はテンプレートのインポート/エクスポート決め打ちだが有用なものがあればアクションを分けて作成
     * 特にAPIで抜けない value mapping などは有用？
     */
    public function post()
    {
        if ($this->debug) {
            echo 'パラメータ';
            echo '<br>';
            var_dump($this->restParams);
        }
        // パラメータサンプル
        //array (size=11)
        //  'endpoint-origin' => string 'http://192.168.242.100/zabbix/api_jsonrpc.php' (length=45)
        //  'source' => string 'http://192.168.242.100/zabbix/api_jsonrpc.php' (length=45)
        //  'destination' => string 'http://zabbix.media-glass.es/api_jsonrpc.php' (length=44)
        //  'id' => string 'Admin' (length=5)
        //  'password' => string 'zabbix' (length=6)
        //  'resource' => string 'template' (length=8)
        //  'hostgroup-source' => string '1' (length=1)
        //  'prefix-old' => string 'ORG' (length=3)
        //  'prefix-new' => string 'ATKK' (length=4)
        //  'templateids' => 
        //    array (size=1)
        //      0 => string '10104' (length=5)
        //  'hostgroup-destination' => string '2' (length=1)
        //  'execute-exchange' => string 'Execute Exchange' (length=16)
        //  'other-endpoint-token' => string '6ea1e8173b8b0dcf1c2589f670bcd354' (length=32)
        // Zabbix API インスタンスを使い分ける
        if ($this->restParams['source'] === $this->restParams['endpoint-origin']) {
            // ログイン中のエンドポイントがソースだったら
            $this->_sourceZabbixApi = $this->plugin->getZabbixApiInstance();
            // ディスティネーションは新規でインスタンスを作る
            $this->_destinationZabbixApi = $this->plugin->getZabbixApiInstance(false);
            $this->_destinationZabbixApi->setEndpoint($this->restParams['destination']);
            $this->_destinationZabbixApi->setToken($this->restParams['other-endpoint-token']);
        } elseif ($this->restParams['source'] === $this->restParams['endpoint-other']) {
            // ログイン中のエンドポイントがディスティネーションだったら
            $this->_destinationZabbixApi = $this->plugin->getZabbixApiInstance();
            // ソースは新規でインスタンスを作る
            $this->_sourceZabbixApi = $this->plugin->getZabbixApiInstance(false);
            $this->_sourceZabbixApi->setEndpoint($this->restParams['source']);
            $this->_sourceZabbixApi->setToken($this->restParams['other-endpoint-token']);
        }
        // フォーマット
        $this->_format = 'json';
        // テンプレート識別子
        if ($this->restParams['prefix-old'] !== '' && $this->restParams['prefix-new'] !== '') {
            $this->_searchTemplateWord = $this->restParams['prefix-old'];
            $this->_replaceTemplateWord = $this->restParams['prefix-new'];
        }
        // ソースホストグループ名を取得
        $this->_sourceHostgroupName = $this->_getHostGroupNameById($this->restParams['hostgroup-source'], 'source');
        // ディスティネーションホストグループ名を取得
        $this->_destinationHostgroupName = $this->_getHostGroupNameById($this->restParams['hostgroup-destination'], 'destination');
        // エラーチェック（とりあえずここでチェックしとく）
        $this->_checkErrors();
        // インポートテンプレート情報
        $templates = $this->_sourceZabbixApi->getTemplate([
            // 所属するホストグループを取得
            'selectGroups' => true,
            'templateids' => $this->restParams['templateids'],
        ]);
        // インポート実行
        $this->_import($templates);
    }
    // テンプレート情報からインポート＆エクスポートを実行
    private function _import($templates)
    {
        // debug
        if ($this->debug) {
            echo '<hr>';
        }
        foreach ($templates as $template) {
            // テンプレートID
            $templateId = $template['templateid'];
            // 所属ホストグループ名
            $templateHostGroupName = reset($template['groups'])['name'];
            // テンプレートをエクスポート
            $export = $this->_sourceZabbixApi->exportConfiguration([
                'options' => [
                    'templates' => [$templateId],
                ],
                'format' => $this->_format,
            ]);
            // トリガーの依存関係がるテンプレートをインポート
            $this->_importDependedTriggers($export);
            // リンクされているテンプレートをインポート
            $this->_importLinkedTemplates($export);
            // ホストグループとテンプレート名を置換
            $importSource = $this->_replaceTemplateString($templateHostGroupName, $this->_destinationHostgroupName, $export);
            // テンプレートをディスティネーションにインポート
            $import = $this->_destinationZabbixApi->importConfiguration([
                'rules' => $this->_importRules,
                'format' => $this->_format,
                'source' => $importSource,
            ]);
            if ($this->debug) {
                echo 'import結果';
                echo '<br>';
                echo '<pre>';
                var_dump($import);
                echo '</pre>';
            }
        }
        // debug
        if ($this->debug) {
            echo '<hr>';
        }
    }
    // リンクされているテンプレートをインポート
    private function _importLinkedTemplates($string)
    {
        // リンクされているテンプレート名を取得
        $linkedTemplateNames = $this->_getLinkedTemplateNames($string);
        // リンクされているテンプレート名がなければ終了
        if (empty($linkedTemplateNames)) {
            // debug
            if ($this->debug) {
                echo 'リンクされているテンプレートなし';
                echo '<br>';
            }
            return false;
        }
        // リンクされているテンプレート情報を取得
        $linkedTemplates = $this->_getTemplatesByName($linkedTemplateNames);

        // debug
        if ($this->debug) {
            echo 'リンクされているテンプレート';
            echo '<br>';
            echo '<pre>';
            var_dump($linkedTemplates);
            echo '</pre>';
        }
        // リンクされているテンプレートをエクスポート＆インポート
        $this->_import($linkedTemplates);
    }
    // トリガーに依存関係のあるテンプレートをインポート
    private function _importDependedTriggers($string)
    {
        if ($this->_format === 'json') {
            $array = json_decode($string, true);
            foreach ($array as $childArray) {
                if (empty($childArray['triggers'])) {
                    // トリガーに依存関係がなかったら抜ける
                    // debug
                    if ($this->debug) {
                        echo 'トリガーに依存関係のあるテンプレートなし';
                        echo '<br>';
                    }
                    continue;
                }
                // トリガーに依存関係があったら先にインポートしておく
                $pattern = '/^{(.*):/';
                foreach ($childArray['triggers'] as $triggers) {
                    foreach ($triggers['dependencies'] as $dependency) {
                        // debug
                        $res = preg_match($pattern, $dependency['expression'], $matches);
                        if ($res !== 1) {
                            // エラーログでも吐いておく
                            var_dump('no matche');
                        }
                        // トリガーに依存関係のあるテンプレート名
                        $dependencyTemplateName = $matches[1];
                        // トリガーに依存関係のあるテンプレート情報
                        $dependedTemplates = $this->_sourceZabbixApi->getTemplate([
                            // 所属するホストグループを取得
                            'selectGroups' => true,
                            'filter' => [
                                'host' => $dependencyTemplateName,
                            ]
                        ]);
                        // error
                        if (empty($dependedTemplates)) {
                            die('依存関係のあるテンプレートを正しく取得できませんでした。');
                        }
                        // debug
                        if ($this->debug) {
                            echo 'トリガーに依存関係のあるテンプレート';
                            echo '<br>';
                            echo '<pre>';
                            var_dump($dependedTemplates);
                            echo '</pre>';
                        }
                        $this->_import($dependedTemplates);
                    }
                }
            }
        } elseif ($this->_format === 'xml') {
            // 未実装
        }
    }
    // テンプレート名からテンプレート情報を取得
    private function _getTemplatesByName($templateNames)
    {
        $templates = $this->_sourceZabbixApi->getTemplate([
            // 所属するホストグループを取得
            'selectGroups' => true,
            'filter' => [
                'host' => $templateNames,
            ]
        ]);
        if (empty($templates)) {
            return false;
        }
        return $templates;
    }
    // ホストグループIDからホストグループ名を取得
    private function _getHostGroupNameById($hostGroupId, $mode)
    {
        if ($mode === 'source') {
            $zabbixApi = $this->_sourceZabbixApi;
        } elseif ($mode === 'destination') {
            $zabbixApi = $this->_destinationZabbixApi;
        }
        // ホストグループ
        $hostgroups = $zabbixApi->getHostgroup([
            'groupids' => $hostGroupId,
        ]);
        if (empty($hostgroups)) {
            return false;
        }
        // ホストグループ名
        return reset($hostgroups)['name'];
    }
    // xml or json 文字列の所属ホストグループとテンプレート名を置換して返す
    private function _replaceTemplateString($hostGroupNameFrom, $hostGroupNameTo, $string)
    {
        // debug
        if ($this->debug) {
            echo '置換前';
            echo '<br>';
            echo '<pre>';
            if ($this->_format === 'json') {
                var_dump(json_decode($string, true));
            } elseif ($this->_format === 'xml') {
                var_dump($string);
            }
            echo '</pre>';
        }
        // テンプレートが所属するホストグループを置換
        if ($this->_format === 'json') {
            $replacedHostGroup = str_replace(json_encode($hostGroupNameFrom), json_encode($hostGroupNameTo), $string);
        } elseif ($this->_format === 'xml') {
            $replacedHostGroup = str_replace($hostGroupNameFrom, $hostGroupNameTo, $string);
        }
        // テンプレート名を置換
        $replacedTemplateName = str_replace($this->_searchTemplateWord, $this->_replaceTemplateWord, $replacedHostGroup);
        // 結果を返す
        $replacedString = $replacedTemplateName;
        // debug
        if ($this->debug) {
            echo '置換後';
            echo '<br>';
            echo '<pre>';
            if ($this->_format === 'json') {
                var_dump(json_decode($replacedString, true));
            } elseif ($this->_format === 'xml') {
                var_dump($replacedString);
            }
            echo '</pre>';
        }
        return $replacedString;
    }
    // xml or json 文字列からリンクされているテンプレート名を取得
    private function _getLinkedTemplateNames($string)
    {
        // テンプレートとのリンクを調べる
        $linkedTemplateNames = [];
        // フォーマットによって処理を分ける
        if ($this->_format === 'json') {
            // Jsonから配列に変換
            $array = json_decode($string, true);
            $linkedTemplateNames = [];
            foreach ($array as $childArray) {
                foreach ($childArray['templates'] as $templates) {
                    foreach ($templates['templates'] as $linkedTemplate) {
                        $linkedTemplateNames[] = $linkedTemplate['name'];
                    }
                }
            }
        } elseif ($this->_format === 'xml') {
            // xmlからオブジェクトに変換
            $object = simplexml_load_string($string);
            foreach ($object->templates->template->templates->template as $templates) {
                $linkedTemplateNames[] = (string) $templates->name;
            }
        }
        return $linkedTemplateNames;
    }
    // エラーチェック
    private function _checkErrors()
    {
        if ($this->_sourceHostgroupName === '') {
            die('ソースホストグループを選択して下さい。');
        }
        if ($this->_destinationHostgroupName === '') {
            die('ディスティネーションホストグループを選択して下さい。');
        }
        if ($this->_sourceZabbixApi === null) {
            die('Source Zabbix API がないよ。');
        }
        if ($this->_destinationZabbixApi === null) {
            die('Destination Zabbix API がないよ。');
        }
        if ($this->_format === 'xml') {
            die('トリガーに依存関係のあるテンプレートインポート機能を作ってないのでxml版はいま使えません。');
        }
    }
}