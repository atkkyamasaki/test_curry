<?php

class SescController extends RestController
{

    public function index()
    {
        // LinuxからloggerでSyslog書込すると
        // 2016-08-30 20:13:08 notice localhost root: {本文}
        // logger "Event received: client (mac=Unknown, ipv4=150.87.56.109) is blocked by (trap.DDI(150.87.56.208)), for reason (Web Reputation (C&C)(100101): Web Reputation Services detected a C&C URL request)"
        // logger "Block 006758@2c:44:fd:27:33:ea for 0 sec., for reason (Web Reputation (C&C)(100101): Web Reputation Services detected a C&C URL request) from port1.0.2(2)@150.87.56.206"
        
        // 擬似配列
        $argv = [
            // $argv[0] スクリプトパス
            'test_program_php',
            // $argv[1] ログメッセージ
            // OpenFlow制御関連ログ
//            '2016-07-27 16: 22:00 openflow WARNING Event received: client (mac=Unknown, ipv4=150.87.56.109) is blocked by (trap.DDI(150.87.56.208)), for reason (Web Reputation (C&C)(100101): Web Reputation Services detected a C&C URL request)',
            // 端末認証結果ログ
            '2016-07-27 16: 22:02 auth INFO Block 006758@2c:44:fd:27:33:ea for 0 sec., for reason (Web Reputation (C&C)(100101): Web Reputation Services detected a C&C URL request) from port1.0.2(2)@150.87.56.206',
            // ダミーログ（どれにもマッチしない）
//            '2016-07-27 16: 22:02 auth INFO TEST LOG',
        ];
        
        var_dump($argv[1]);
        
        if (!isset($argv)) {
            return;
        }
        
        if (!isset($argv[1])) {
            return;
        }
        
        // ログメッセージ
        $message = $argv[1];
        
        // ログメッセージを整形
        $formattedMessge = $this->formatMessage($message);
        
        if (!isset($formattedMessge)) {
            return;
        }
        
        // ログテキストを作成
        $logText = $this->createLogText($formattedMessge);
        
        // ログを書き込み
        $this->log($logText);
        
        // ページタイトル
        $this->view->setTitle('SESC API');
    }
    
	public function formatMessage($message)
    {
        // ログ形式をチェック
        $patterns = [
            /**
             * OpenFlow制御関連
             * Block event received フォーマット
             * openflow	WARN	Event received: client {DEVICE-NAME}(mac={MAC-ADDRESS},ip={IPADDRESS} user={USERNAME}) is blocked by ({OWNER}), for reason({REASON}), belonged to {OLD-VLAN} from {PORT-NAME}({PORT-NUM})@{SWITCH-IP}
             * サンプルログ
             * 2016-07-27 16: 22:00 openflow WARNING Event received: client (mac=Unknown, ipv4=150.87.56.109) is blocked by (trap.DDI(150.87.56.208)), for reason (Web Reputation (C&C)(100101): Web Reputation Services detected a C&C URL request)
             */
            // Linux logger検証
//            'openflowEventReceived' => '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}) notice localhost root: Event received: client \(mac=(.*), ipv4=(.*)\) is blocked by \((.*)\((.*)\)\), for reason \((.*): (.*)\)$/',
            // 本番
            'openflowEventReceived' => '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}: [\d]{2}:[\d]{2}) openflow WARNING Event received: client \(mac=(.*), ipv4=(.*)\) is blocked by \((.*)\((.*)\)\), for reason \((.*): (.*)\)$/',
            /**
             * 端末認証結果
             * Block フォーマット
             * auth	INFO	Block {USERNAME}@{MAC-ADDRESS} to ({NETWORK-NAME}) for {TIME} sec., for reason ({REASON}) from {PORT-NAME}({PORT-NUM})@{SWITCH-IP}
             * サンプルログ
             * 2016-07-27 16: 22:02 auth INFO Block 006758@2c:44:fd:27:33:ea for 0 sec., for reason (Web Reputation (C&C)(100101): Web Reputation Services detected a C&C URL request) from port1.0.2(2)@150.87.56.206
             */
            // Linux logger検証
//            'authBlock' => '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}) notice localhost root: Block (.*)@(.*) for (.*) sec., for reason \((.*): (.*)\) from (.*)@(.*)$/',
            // 本番
            'authBlock' => '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}: [\d]{2}:[\d]{2}) auth INFO Block (.*)@(.*) for (.*) sec., for reason \((.*): (.*)\) from (.*)@(.*)$/',
        ];
        $formattedMessge = null;
        foreach ($patterns as $type => $pattern) {
            $isMatched = preg_match($pattern, $message, $matches);
            if ($isMatched && !empty($matches)) {
                $formattedMessge = [
                    'type' => $type,
                    'data' => $matches,
                ];
                break;
            }
        }
        return $formattedMessge;
    }
    
    /**
     * ログテキストを作成
     */
	public function createLogText($formattedMessge)
    {
        $logText = null;
        switch ($formattedMessge['type']) {
            case 'openflowEventReceived':
                // 元のログメッセージ
                $rowMessage = isset($formattedMessge['data'][0]) ? $formattedMessge['data'][0] : null;
                // 時間
                $time = isset($formattedMessge['data'][1]) ? $this->trimTime($formattedMessge['data'][1]) : null;
                // クライアントMACアドレス
                $client['macAddress'] = isset($formattedMessge['data'][2]) ? $formattedMessge['data'][2] : null;
                // クライアントIPアドレス
                $client['ipAddress'] = isset($formattedMessge['data'][3]) ? $formattedMessge['data'][3] : null;
                // オーナー名
                $owner['name'] = isset($formattedMessge['data'][4]) ? $formattedMessge['data'][4] : null;
                // オーナーIPアドレス
                $owner['ipAddress'] = isset($formattedMessge['data'][5]) ? $formattedMessge['data'][5] : null;
                // 理由コード
                $reason['code'] = isset($formattedMessge['data'][6]) ? $formattedMessge['data'][6] : null;
                // 理由説明
                $reason['description'] = isset($formattedMessge['data'][7]) ? $formattedMessge['data'][7] : null;
                
                // ログテキストを整形
                $logText = 
                    $time . ' ' .
                    'openflow ' .
                    'WARNING ' .
                    '[SESCがOpenFlowイベントを検出しました] ' .
                    'クライント(' .
                        'MACアドレス=' . $client['macAddress'] . ', ' . 
                        'IPアドレス=' . $client['ipAddress'] . 
                    ') ' .
                    'アプリケーション(' .
                        '名前=' . $owner['name'] . ', ' . 
                        'IPアドレス=' . $owner['ipAddress'] . 
                    ') ' .
                    '検出イベント(' .
                        'コード=' . $reason['code'] . ', ' . 
                        '詳細=' . $reason['description'] . 
                    ') ';
                break;
                
            case 'authBlock':
                // 元のログメッセージ
                $rowMessage = isset($formattedMessge['data'][0]) ? $formattedMessge['data'][0] : null;
                // 時間
                $time = isset($formattedMessge['data'][1]) ? $this->trimTime($formattedMessge['data'][1]) : null;
                // クライアント名
                $client['name'] = isset($formattedMessge['data'][2]) ? $formattedMessge['data'][2] : null;
                // クライアントMACアドレス
                $client['macAddress'] = isset($formattedMessge['data'][3]) ? $formattedMessge['data'][3] : null;
                // 処理時間？
                $sec = isset($formattedMessge['data'][4]) ? $formattedMessge['data'][4] : null;
                // 理由コード
                $reason['code'] = isset($formattedMessge['data'][5]) ? $formattedMessge['data'][5] : null;
                // 理由説明
                $reason['description'] = isset($formattedMessge['data'][6]) ? $formattedMessge['data'][6] : null;
                // オーナーポート
                $owner['port'] = isset($formattedMessge['data'][7]) ? $formattedMessge['data'][7] : null;
                // オーナーIPアドレス
                $owner['ipAddress'] = isset($formattedMessge['data'][8]) ? $formattedMessge['data'][8] : null;
                
                // SESC API からクライアント情報を取得
                $apiClient = $this->_getClients($client['macAddress']);
                // クライアントIPアドレス
                $client['ipAddress'] = isset($apiClient['switch']['ipv4']) ? $apiClient['switch']['ipv4'] : null;
                // クライアントユーザ名
                $client['username'] = isset($apiClient['user.name']) ? $apiClient['user.name'] : null;
                // クライアントデバイス名
                $client['devicename'] = isset($apiClient['device.name']) ? $apiClient['device.name'] : null;
                // クライアントVLAN ID
                $client['vlanId'] = isset($apiClient['vlan_id']) ? $apiClient['vlan_id'] : null;
                
                // ログテキストを整形
                $logText = 
                    $time . ' ' .
                    'openflow ' .
                    'WARNING ' .
                    '[SESCが端末を遮断しました] ' .
                    'クライント(' .
                        'ユーザ名=' . $client['username'] . ', ' . 
                        'デバイス名=' . $client['devicename'] . ', ' . 
                        'IPアドレス=' . $client['ipAddress'] . ', ' . 
                        'VLAN ID=' . $client['vlanId'] . ', ' . 
                        'MACアドレス=' . $client['macAddress'] . 
                    ') ' .
                    'アプリケーション(' .
                        'ポート=' . $owner['port'] . ', ' . 
                        'IPアドレス=' . $owner['ipAddress'] . 
                    ') ' .
                    '原因イベント(' .
                        'コード=' . $reason['code'] . ', ' . 
                        '詳細=' . $reason['description'] . 
                    ') ';
                break;
            
            default:
                break;
        }
        $logText .= "\n";
        return $logText;
    }
    
    /*
     * 時間の不要なスペースを削除
     * 16: 22:00 => 16:22:00
     */
    private function trimTime($time)
    {
        // preg_replace
        // マッチしなければ元の $time を返す
        // 失敗すると null を返す
        return preg_replace('/:\s+/', ':', $time);
    }
    
    /**
     * SESC API から クライアント情報を取得
     * 
     * クライアント一覧
     * http://150.87.56.205/api/sesc/1/clients
     * 
     * MACアドレスからクライントを絞り込み
     * http://150.87.56.205/api/sesc/1/clients/mac
     */
	private function _getClients($macAddress = null)
    {
        // SESC API エンドポイント - 検証環境
        $endpoint = 'http://192.168.242.100/sesc/clients';
        // SESC API エンドポイント
//        $endpoint = 'http://150.87.56.205/api/sesc/1/clients';
        if (!is_null($macAddress)) {
            // MACアドレスがなかったらクライアント一覧
//            $url = $endpoint . '/mac/' . $macAddress;
            // MACアドレスがなかったらクライアント一覧 - 検証
            $url = $endpoint . '/mac/test/';
        } else {
            // MACアドレスがなかったら絞り込み
            $url = $endpoint;
        }
        // APIから結果を取得
        $response = $this->_curl($url);
        // TODO エラーチェック
        return json_decode($response, true);
    }
    
    /**
     * ログを書き込み
     */
	public function log($logText)
    {
        // 1ファイルあたりのログの最大行
        $maxLines = 1000;
        // ログディレクトリ
//        $dirPath = '/root/test/';
        $dirPath = '/mnt/hgfs/htdocs/api-test/site/logs/';
        // ログファイル名
		$fileName = 'sesc';
        // ファイルパス
		$filePath = $dirPath . '/' . $fileName;
        if (file_exists($filePath) && is_file($filePath) && !is_writable($filePath)) {
            // ファイルが存在しないか書き込み権限がなければ終了
            return;
        }
        // ログファイルの行数を取得
		$lineCount = 0;
		if (file_exists($filePath)) {
			$lineCount = count(file($filePath));
		}
        // ローテート
		if ($lineCount >= $maxLines) {
            // ログが最大業を超えていたらローテートを実行
			$this->_rotate($dirPath, $fileName);
		}
        // ログ書き込み
		$r = @fopen($filePath, 'a');
		@fputs($r, $logText);
		@fclose($r);
    }
    
    /**
     * ログローテート
     */
	private function _rotate($dirPath, $fileName)
	{
        // ローテート世代数
        $_generation = 5;
		// ディレクトリからファイルリストを取得
    	$dir = dir($dirPath);
    	$generations = [];
    	while ($content = $dir->read()) {
    		$path = sprintf("%s/%s", $dirPath, $content);
    		if (!is_file($path)) {
    			// ファイル以外は無視
    			continue;
    		}
    		$info = pathinfo($path);
    		$name = $info['basename'];
    		if (!preg_match(sprintf('|^%s(\.[0-9]+)?$|', $fileName), $name)) {
    			// ログファイル名にマッチしないものは無視
    			continue;
    		}
    		$generation = trim(str_replace($fileName, '', $name), '.');
    		if ($generation === '') {
    			$generation = 0;
    		}
    		if ($generation >= $_generation - 1) {
    			// 最大世代数を超えていたら削除
    			@unlink($path);
    		} else {
    			$generations[] = $generation;
    		}
    	}
    	// ローテート数の拡張子を付ける
    	rsort($generations);
    	foreach ($generations as $gene) {
    		$oldName = sprintf('%s/%s.%s', $dirPath, $fileName, $gene);
    		$newName = sprintf('%s/%s.%s', $dirPath, $fileName, $gene + 1);
    		@rename($oldName, $newName);
    	}
    	$filePath = sprintf("%s/%s", $dirPath, $fileName);
    	@rename($filePath, $filePath . '.1');
    	$dir->close();
	}
    
    /**
     * cURL
     */
    private function _curl($url)
    {
        // サンプル
        //curl --user manager:2FSVRoom http://150.87.56.205/api/sesc/1/clients | python -mjson.tool
        //curl --user manager:2FSVRoom http://150.87.56.205/api/sesc/1/clients/mac/28:e3:47:8e:ce:0a | python -mjson.tool
        // アカウント 検証環境
        $username = 'admin';
        $password = 'password';
        // アカウント 本番環境
//        $username = 'manager';
//        $password = '2FSVRoom';
        $timeout = 30;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // 検証環境はプロキシを通す
//        if ($_SERVER['SERVER_NAME'] === '192.168.242.100' || $_SERVER['SERVER_NAME'] === 'localhost.api-test') {
//            curl_setopt($ch, CURLOPT_PROXYPORT, '3128');
//            curl_setopt($ch, CURLOPT_PROXY, 'http://http-gw.allied-telesis.co.jp');
//        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    
    /**
     * 擬似 SESC API
     * /sesc/clients or /sesc/clients/mac/$MAC_ADDRESS
     */
    public function clients()
    {
        $this->view->enableRendering(false);
        $response = null;
        $clientsJson = '{"items": [{"status": "permit", "device.id": 170, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:2f:6f:89", "user.name": "018198", "device.name": "大槻　祐介　有線LAN", "user.id": 95, "vlan_id": 0}, {"status": "permit", "device.id": 414, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:2f:6f:8a", "user.name": "018198", "device.name": "大槻　祐介　有線LAN", "user.id": 95, "vlan_id": 0}, {"status": "permit", "device.id": 248, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:31:d0:90", "user.name": "004517", "device.name": "田中　雅雄　有線LAN", "user.id": 115, "vlan_id": 0}, {"status": "permit", "device.id": 502, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "00:09:41:bb:2b:91", "user.name": "ISv57", "device.name": "9424T/SP-6", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 511, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "00:0a:79:e6:07:12", "user.name": "ISv57", "device.name": "ネットワークカメラ-4", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 516, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "00:30:6e:fb:3b:14", "user.name": "SalesPrinter", "device.name": "営業プリンター4", "user.id": 178, "vlan_id": 0}, {"status": "permit", "device.id": 548, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "00:0a:79:e6:10:15", "user.name": "ISv57", "device.name": "ネットワークカメラ-5", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 326, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:27:34:18", "user.name": "017027", "device.name": "東　美佐　有線LAN", "user.id": 133, "vlan_id": 0}, {"status": "permit", "device.id": 104, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:27:31:19", "user.name": "022942", "device.name": "秋山 卓矢　有線LAN", "user.id": 79, "vlan_id": 0}, {"status": "permit", "device.id": 503, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "00:09:41:bb:14:9e", "user.name": "ISv57", "device.name": "9424T/SP-7", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 289, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:22:43:23", "user.name": "004292", "device.name": "西岡 　史敏　有線LAN", "user.id": 124, "vlan_id": 0}, {"status": "permit", "device.id": 565, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "b8:6b:23:90:c3:25", "user.name": "011780", "device.name": "八木沢　崇裕　有線LAN", "user.id": 211, "vlan_id": 0}, {"status": "permit", "device.id": 140, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:21:08:27", "user.name": "005516", "device.name": "礒田　治 　有線LAN", "user.id": 88, "vlan_id": 0}, {"status": "permit", "device.id": 159, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "b8:6b:23:c5:c1:23", "user.name": "003212", "device.name": "梅本　宏　有線LAN", "user.id": 92, "vlan_id": 0}, {"status": "permit", "device.id": 452, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:2f:2f:31", "user.name": "005543", "device.name": "木村　有司　有線LAN", "user.id": 168, "vlan_id": 0}, {"status": "permit", "device.id": 556, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:32:49:b3", "user.name": "019186", "device.name": "井上　真弓　有線LAN", "user.id": 204, "vlan_id": 0}, {"status": "permit", "device.id": 553, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:31:d2:a3", "user.name": "018171", "device.name": "植木　修一　有線LAN", "user.id": 202, "vlan_id": 0}, {"status": "permit", "device.id": 496, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:27:31:36", "user.name": "000332", "device.name": "長尾　利彦　有線LAN", "user.id": 181, "vlan_id": 0}, {"status": "permit", "device.id": 460, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:31:d2:b7", "user.name": "002798", "device.name": "鈴木　勲　有線LAN", "user.id": 170, "vlan_id": 0}, {"status": "permit", "device.id": 539, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "70:f3:95:12:26:38", "user.name": "003095", "device.name": "山住　英孝　有線LAN2", "user.id": 195, "vlan_id": 0}, {"status": "permit", "device.id": 186, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:33:45:3a", "user.name": "013049", "device.name": "小澤　剛　有線LAN", "user.id": 99, "vlan_id": 0}, {"status": "permit", "device.id": 285, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:2f:2a:bb", "user.name": "004013", "device.name": "中邨　由里子　有線LAN", "user.id": 123, "vlan_id": 0}, {"status": "permit", "device.id": 395, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:2f:6f:c7", "user.name": "002852", "device.name": "小林　 浩二　有線LAN", "user.id": 152, "vlan_id": 0}, {"status": "permit", "device.id": 538, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "70:f3:95:12:26:48", "user.name": "003095", "device.name": "山住　英孝　有線LAN1", "user.id": 195, "vlan_id": 0}, {"status": "permit", "device.id": 510, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "00:0a:79:e6:09:33", "user.name": "ISv57", "device.name": "ネットワークカメラ-3", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 451, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:2f:6f:ca", "user.name": "021695", "device.name": "安島　恵理　有線LAN", "user.id": 167, "vlan_id": 0}, {"status": "permit", "device.id": 436, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:2f:6a:4c", "user.name": "021008", "device.name": "赤松　竜盛　有線LAN", "user.id": 163, "vlan_id": 0}, {"status": "permit", "device.id": 546, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "00:01:e6:7b:ba:cd", "user.name": "ISv57", "device.name": "MFGプリンタサーバ", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 387, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:33:45:4e", "user.name": "005327", "device.name": "中筋　恭子　有線LAN", "user.id": 150, "vlan_id": 0}, {"status": "permit", "device.id": 495, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:27:31:4f", "user.name": "020451", "device.name": "新井　章治　有線LAN", "user.id": 180, "vlan_id": 0}, {"status": "permit", "device.id": 327, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:22:96:50", "user.name": "015610", "device.name": "肥野 　貴義　有線LAN", "user.id": 134, "vlan_id": 0}, {"status": "permit", "device.id": 589, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:33:40:51", "user.name": "023191", "device.name": "岡崎　隆行　有線LAN", "user.id": 96, "vlan_id": 0}, {"status": "permit", "device.id": 313, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:32:48:53", "user.name": "023418", "device.name": "橋本　俊佑　有線LAN", "user.id": 130, "vlan_id": 0}, {"status": "permit", "device.id": 345, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:33:40:58", "user.name": "000800", "device.name": "細谷　均　有線LAN", "user.id": 138, "vlan_id": 0}, {"status": "permit", "device.id": 280, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:22:95:da", "user.name": "018074", "device.name": "出口　伸也　有線LAN", "user.id": 122, "vlan_id": 0}, {"status": "fail", "mac": "20:c6:eb:8e:ae:3a", "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "vlan_id": null}, {"status": "permit", "device.id": 264, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "b8:6b:23:c6:c2:25", "user.name": "012887", "device.name": "辻　裕紀夫　有線LAN", "user.id": 118, "vlan_id": 0}, {"status": "permit", "device.id": 111, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:26:03:62", "user.name": "018619", "device.name": "秋吉　孝利　有線LAN", "user.id": 81, "vlan_id": 0}, {"status": "permit", "device.id": 542, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "1c:6f:65:00:5e:3b", "user.name": "003095", "device.name": "山住　英孝　有線LAN5", "user.id": 195, "vlan_id": 0}, {"status": "permit", "device.id": 364, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:27:31:69", "user.name": "022781", "device.name": "村田　 俊哉　有線LAN", "user.id": 143, "vlan_id": 0}, {"status": "permit", "device.id": 544, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "44:37:e6:b8:13:6c", "user.name": "022322", "device.name": "中山　正成　有線LAN", "user.id": 197, "vlan_id": 0}, {"status": "permit", "device.id": 310, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:33:45:31", "user.name": "019291", "device.name": "根本　誠　有線LAN", "user.id": 129, "vlan_id": 0}, {"status": "permit", "device.id": 217, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "b8:6b:23:47:b5:73", "user.name": "023205", "device.name": "小林　剛　有線LAN", "user.id": 108, "vlan_id": 0}, {"status": "permit", "device.id": 109, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "b0:c7:45:4a:9d:f4", "user.name": "NAS", "device.name": "営業NAS　メイン", "user.id": 80, "vlan_id": 0}, {"status": "permit", "device.id": 462, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "2c:44:fd:1e:3a:92", "user.name": "000422", "device.name": "小野　俊雄　有線LAN", "user.id": 171, "vlan_id": 0}, {"status": "permit", "device.id": 461, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.2", "no": 2}}, "mac": "70:f3:95:0a:dc:69", "user.name": "002798", "device.name": "鈴木　勲　無線LAN", "user.id": 170, "vlan_id": 0}, {"status": "permit", "device.id": 586, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:27:31:79", "user.name": "011600", "device.name": "清野　剛　有線LAN", "user.id": 223, "vlan_id": 0}, {"status": "permit", "device.id": 501, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "00:09:41:ba:a5:7c", "user.name": "ISv57", "device.name": "9424T/SP-5", "user.id": 183, "vlan_id": 0}, {"status": "permit", "device.id": 445, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.3", "no": 3}}, "mac": "2c:44:fd:32:48:7e", "user.name": "003635", "device.name": "小泉　卓也　有線LAN", "user.id": 165, "vlan_id": 0}, {"status": "permit", "device.id": 588, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:33:40:7f", "user.name": "023213", "device.name": "新貝　達朗　有線LAN", "user.id": 110, "vlan_id": 0}], "sort_how": "asc", "sort_by": null, "limit": 50, "offset": 0, "keywords": [], "total": 133}';
        if (isset($this->params[0]) && $this->params[0] === 'mac' && $this->params[1] === 'test') {
            // クライアント絞り込み（MACアドレス） - 検証
            $clients = json_decode($clientsJson, true);
            // とりあえずランダムに取り出す
            $response = json_encode($clients['items'][array_rand($clients['items'])]);
        } elseif (isset($this->params[0]) && $this->params[0] === 'mac') {
            // クライアント絞り込み（MACアドレス）
            $response = '{"status": "permit", "device.id": 588, "switch": {"ipv4": "150.87.57.207", "port": {"name": "port1.0.4", "no": 4}}, "mac": "2c:44:fd:33:40:7f", "user.name": "023213", "device.name": "田中　太郎　有線LAN", "user.id": 110, "vlan_id": 0}';
        } else {
            // クライアント一覧（50件表示）
            $response = $clientsJson;
        }
        echo $response;
    }

}
