<?php

class MibsController extends RestController
{

    public function index()
    {
        // MIBファイル・ディレクトリ一覧を取得
        $mibsDirectory = Ini::load('config.ini', 'mib directory')[$this->appEnv];

        // MIBディレクトリを走査
        $mibs = $this->_searchDirectory($mibsDirectory);

        // MIBファイル一覧をビューに割当
        $this->view->mibs = $mibs;

        // ページタイトル
        $this->view->setTitle('MIBs');
    }

    private function _searchDirectory($directory)
    {
        // 保存済ファイルの検索
        $handle = @opendir($directory);
        if ($handle) {
            $filename = null;
            // ディレクトリを捜査
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $filename[] = $file;
                }
            }
            closedir($handle);
        } else {
            return null;
        }

        foreach ($filename as $file) {
            var_dump($directory . DIRECTORY_SEPARATOR . $file);
        }

        return $filename;
    }

}