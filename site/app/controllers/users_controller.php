<?php

/**
 * UsersController
 */
class UsersController extends RestController
{

    public function login()
    {
        // エンドポイント一覧を取得
        $endpoints = Ini::load('config.ini', 'endpoints');
        $this->view->endpoints = $endpoints;

        // ページタイトル
        $this->view->setTitle('Login');
    }

    /**
     * TODO Ajax通信時の処理を共通化したい
     */
    public function loginPost()
    {
        // Zabbix API
        $isSingleton = true;
        if ($this->request->isXmlHttp()) {
            // Ajax通信だったら新しいインスタンスを生成
            $isSingleton = false;
        }
        $zabbixApi = $this->plugin->getZabbixApiInstance($isSingleton);
        
//        var_dump($this->restParams);
//        var_dump($isSingleton);
//        var_dump($zabbixApi);
        
        // API エンドポイントを設定
        $zabbixApi->setEndpoint($this->restParams['endpoint']);

        // ログイン実行
        $token = $zabbixApi->loginUser([
            'user' => $this->restParams['id'],
            'password' => $this->restParams['password'],
        ]);

        // Tokenチェック
        $user = null;
        if (isset($token) && strlen($token) === 32) {
            // API トークンを設定
            $zabbixApi->setToken($token);
            // ユーザ情報取得
            $user = $zabbixApi->loginUser([
                'user' => $this->restParams['id'],
                'password' => $this->restParams['password'],
                'userData' => true,
            ]);
        }
        
//        var_dump($zabbixApi);
//        var_dump($user);
//        var_dump($token);
        
        // ユーザ情報チェック
        if (isset($user)) {
            // Ajax通信の場合はtokenを返して処理を終了
            if ($this->request->isXmlHttp()) {
                $user['token'] = $token;
//                array_merge($user, ['token' => $token]);                
                $this->response->json($user);
                return;
            }
            // セッションにユーザ情報を格納
            $session = new Session('user');
            $session->auth = true;
            $session->endpoint = $this->restParams['endpoint'];
            $session->token = $token;
            $session->user = $user;
            $this->redirect('/');
        }
        
        // Ajax通信の場合はfalseを返して処理を終了
        if ($this->request->isXmlHttp()) {
            $this->response->json(false);
            return;
        }
        $this->redirect('/login');
    }

    public function logout()
    {
        // Zabbix API
        $zabbixApi = $this->plugin->getZabbixApiInstance();

        // ログアウト実行
        $logout = $zabbixApi->logoutUser();

        if (!$logout) {
            // TODO ログイン失敗したら
        }

        // ログアウト成功ならセッションもクリア
        $session = new Session('user');
        $session->clear();

        $this->redirect('/login');
    }

}