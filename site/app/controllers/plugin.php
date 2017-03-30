<?php

class Plugin extends PluginAbstract
{
    public $user = null;
    public $resources = null;
    public $menus = null;
    private $_endpoint = null;
    private $_token = null;
    private $_zabbixApi = null;
    private $_zabbixApiPrivilege = self::ZABBIX_SUPER_ADMIN;
    
    const ZABBIX_USER = 1;
    const ZABBIX_ADMIN = 2;
    const ZABBIX_SUPER_ADMIN = 3;

    /**
     * 共通処理（コントローラ呼び出し前）
     */
    public function preProcess()
    {
        // オートリダイレクト時のヒストリ
        $this->getAutoRedirectHistory();

        // エンコーディングを UTF-8 に設定
        $this->view->setOutputEncoding('UTF-8');

        // ユーザ認証セッション開始
        $session = new Session('user');

        // ログインチェック
        // SESC APIテスト用に一時的に /sesc配下 のみログイン不要で開放
        if ($this->request->getController() !== 'sesc') {
            if ($this->request->getAction() !== 'login' && !$session->exists('auth')) {
                $session->clear();
                $this->redirect('/login');
            }
        }
        
//        echo 'Session';
//        echo '<br>';
//        var_dump($session->auth);
//        var_dump($session->user);
//        var_dump($session->endpoint);
//        var_dump($session->token);
//        echo '<hr>';
        // セッションからユーザ情報をセット
        $this->user = $session->user;

        // セッションから認証情報をセット
        $this->_endpoint = $session->endpoint;
        $this->_token = $session->token;

        // ユーザ情報をビューに設定
        $this->view->user = $this->user;
        
        // エンドポイントをビューに設定
        $this->view->endpoint = $session->endpoint;

        // Zabbixオブジェクト一覧を取得
        $resourcesIni = Ini::load('zabbix.ini');
        $resources = array_keys($resourcesIni);
        $this->resources = $resources;
        
        // ユーザ権限チェック＆アクセス制限
        if (isset($this->user['type'])) {
            // Zabbix Super User のみ Zabbix API 操作メニュー表示
            if ($this->user['type'] >= $this->_zabbixApiPrivilege) {
                $this->view->resources = $resources;
            }
            
            // ユーザ権限別に利用可能なその他メニューを読み込む
            $menusIni = Ini::load('menu.ini', $this->user['type']);
            $menus = explode(',', $menusIni['menu']);
            $this->menus = $menus;
            $this->view->menus = $menus;

            // ユーザ権限チェック
            $controller = $this->request->getController();
            // Zabbix API 基本メニューへのアクセス権限をチェック
            if ($controller === 'api' && (int) $this->user['type'] < $this->_zabbixApiPrivilege) {
                throw new MemberInaccessibleException($controller, isset($this->user['alias']) ? $this->user['alias'] : 'Unknown');
            }

            // Zabbix API 基本メニュー以外へのアクセス権限をチェック
            if ($controller !== 'index' && $controller !== 'users' && $controller !== 'api') {
                // 当該ユーザ権限のメニュー一覧にあるかチェック
                $isAllowedMenu = in_array($controller, $this->menus);
                if (! $isAllowedMenu) {
                    throw new MemberInaccessibleException($controller, isset($this->user['alias']) ? $this->user['alias'] : 'Unknown');
                }
            }
        }
                
        // ヘルパをビューに設定
        $this->view->helper = new Helper();

        // css を設定
        $stylesheets = [
            'common.min.css',
            // 'common.css',
        ];
        foreach ($stylesheets as $css) {
            $this->view->addPreferredCss($css);
        }

        // javascript を設定
        $javascripts = [
            'jquery.min.js',
            'jquery.cookie.min.js',
            'common.min.js',
        ];
        foreach ($javascripts as $js) {
            $this->view->addPreferredJs($js);
        }
    }
    /*
     * ヒストリ記録
     * 自動リダイレクト後に利用する各種情報をセッションに格納
     */

    public function getAutoRedirectHistory()
    {
        if ($this->request->getMethod() === 'GET') {
            if ($this->_controllerInstance->session->exists('errors')) {
                $this->view->errors = $this->_controllerInstance->session->errors;
                $this->_controllerInstance->session->remove('errors');
            }
            if ($this->_controllerInstance->session->exists('input_params')) {
                $this->view->input_params = $this->_controllerInstance->session->input_params;
                $this->_controllerInstance->session->remove('input_params');
            }
            $this->_controllerInstance->session->back_path = $this->request->getPath();
        }
    }

    /**
     * 自動リダイレクト
     * POST/PUT/DELETE処理エラー時に自動で元のページヘリダイレクト
     */
    public function autoRedirect($errors, $inputParams)
    {
        $this->_controllerInstance->session->errors = $errors;
        $this->_controllerInstance->session->input_params = $inputParams;
        $this->redirect($this->_controllerInstance->session->back_path);
    }

    /**
     * ZabbixAPIインスタンスを取得
     * TODO Ajax用と通常用をうまく切り替えたい
     *
     * @return ZabbixApi instance
     */
    public function getZabbixApiInstance($isSingleton = true)
    {
        // 新しいインスタンスを返す場合
        if ($isSingleton === false) {
            return new ZabbixApi();
        }
        // 一意なインスタンスを保持する場合
        if (!($this->_zabbixApi instanceof ZabbixApi)) {
            $this->_zabbixApi = new ZabbixApi();
            if (isset($this->_endpoint) && isset($this->_token)) {
                // エンドポイントをセット
                $this->_zabbixApi->setEndpoint($this->_endpoint);
                // トークンをセット
                $this->_zabbixApi->setToken($this->_token);
            }
        }
        return $this->_zabbixApi;
    }

    /**
     * ポストプロセス
     */
    public function postProcess()
    {
        
    }

}
