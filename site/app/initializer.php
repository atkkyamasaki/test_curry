<?php

/**
 * Initializer
 */
class Initializer extends InitializerStandard
{

    /**
     * イニシャライズ
     */
    public function initialize()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        date_default_timezone_set('Asia/Tokyo');

        // include_pathを追加
        set_include_path(get_include_path() . PATH_SEPARATOR . SITE_PATH . '/library');

        // iniファイルの読み込みを配列にする
        Ini::isReturnObject(false);
    }

    /**
     * 本番環境
     */
    public function initProduct()
    {
        ini_set('display_errors', 0);
    }

    /**
     * 開発環境
     */
    public function initDevelop()
    {
        // BASIC認証
        Loader::load('BasicAuth', 'utility');
        $auth = new BasicAuth();
        $auth->setMessages(array(
            'required' => 'Authorization Required.',
            'denied' => 'Access Denied.',
            'prompt' => 'Please Enter Your Password'
        ));
        $auth->file(false);
    }

    /**
     * ローカル開発環境
     */
    public function initTest()
    {
        // BASIC認証
        Loader::load('BasicAuth', 'utility');
        $auth = new BasicAuth();
        $auth->setMessages(array(
            'required' => 'Authorization Required.',
            'denied' => 'Access Denied.',
            'prompt' => 'Please Enter Your Password'
        ));
        $auth->file(false);
    }

}