<?php

/**
 * ValuemappingsController
 */
class ValuemappingsController extends RestController
{

    public function index()
    {
        // ページタイトル
        $this->view->setTitle('Valuemappings');
    }
    
    public function export()
    {
        // Export
        $valuemappingService = $this->service('ValuemappingService');
        $json = $valuemappingService->export();
        $this->view->json = $json;
        // ファイルに書き出す
//        file_put_contents(PathManager::getHtdocsDirectory() . '/json/valuemapping_' . time(), $json);
        // ページタイトル
        $this->view->setTitle('Valuemappings - Export');
    }

    public function csvPost()
    {
//        array (size=13)
//          0 => 
//            array (size=3)
//              'valuemapid' => string '4' (length=1)
//              'name' => string 'APC Battery Replacement Status' (length=30)
//              'mappings' => 
//                array (size=7)
//                  0 => 
//                    array (size=4)
//                      'mappingid' => string '26' (length=2)
//                      'valuemapid' => string '4' (length=1)
//                      'value' => string '1' (length=1)
//                      'newvalue' => string 'unknown' (length=7)
//                  1 => 
//                    array (size=4)
//                      'mappingid' => string '27' (length=2)
//                      'valuemapid' => string '4' (length=1)
//                      'value' => string '2' (length=1)
//                      'newvalue' => string 'notInstalled' (length=12)
//          1 => 
//            array (size=3)
//              'valuemapid' => string '5' (length=1)
//              'name' => string 'APC Battery Status' (length=18)
//              'mappings' => 
//                array (size=3)
//                  0 => 
//                    array (size=4)
//                      'mappingid' => string '23' (length=2)
//                      'valuemapid' => string '5' (length=1)
//                      'value' => string '1' (length=1)
//                      'newvalue' => string 'unknown' (length=7)        
        // CSV操作
        $csvManipulator = new CsvManipulator();
        $csvManipulator->validate($_FILES['csv']['error']);
        // CSVファイルをデータディレクトリに移動
//        $filename = PathManager::getDataDirectory() . $_FILES['csv']['tmp_name'];
        $filename = $_FILES['csv']['tmp_name'];
        move_uploaded_file($_FILES['csv']['tmp_name'], $filename);
        
        // CSVファイルを文字列として読み込み
        $csvString = file_get_contents($filename);

        // 現在の文字コードを判定
        $fromEncoding = $this->_detectEncoding($csvString);

        // 文字コード変換を実行
        $toEncoding = 'UTF-8';
        $encodedCsvString = mb_convert_encoding($csvString, $toEncoding, $fromEncoding);

        // 変換データをファイルに書き込み
        file_put_contents($filename, $encodedCsvString);

        // CSVファイルを開く
        $fp = fopen($filename, 'r');

        // CSVファイルを配列に格納
        $rows = [];
        // all
        $i = 0;
        // valuemapid
        $j = 0;
        // mappingid
        $k = 0;
        while ($row = fgetcsv($fp)) {
            if ($row === array(null)) {
                // 空行はスキップ
                continue;
            }
            if ($row[0] !== '') {
                $rows[$j]['valuemapid'] = $j;
                $rows[$j]['name'] = $row[0];
                $rows[$j]['mappings'] = [];
                $j++;
            }
            if ($row[1] !== '' && $row[2] !== '') {
                $rows[$j - 1]['mappings'][$k] = [
                    'mappingid' => $i,
                    'valuemapid' => $j - 1,
                    'value' => $row[1],
                    'newvalue' => $row[2],
                ];
                $k++;
            }
            $i++;
        }
        
        if (!feof($fp)) {
            // ファイルポインタが終端に達していなければエラー
            throw new Exception('CSVパースエラー。正しいファイルを選択して下さい。');
        }
        
        fclose($fp);
        $json = json_encode($rows, JSON_UNESCAPED_UNICODE);
        
//        unlink($filename);
        
        echo '<p><a href="/valuemappings">戻る</a></p>';
        echo '<textarea cols="100" rows="50">' . $json . '</textarea>';
    }
            
    public function import()
    {
        // ページタイトル
        $this->view->setTitle('Valuemappings - Import');
    }
    
    public function importPost()
    {
        $json = isset($this->restParams['json']) ? $this->restParams['json'] : null;
        
        if ($json === '' || $json === null) {
            $this->redirect('valuemappings/import');
        }
        
        // Import
        $valuemappingService = $this->service('ValuemappingService');
        $results = $valuemappingService->import($json);
        $this->view->results = $results;
    }
    
    /**
     * 前後のホワイトスペースを取り除く
     * php標準のtrim()だと全角スペースに対応していないため
     * u修飾子の性質よりUTF-8エンコーディングが壊れていたときにはNULLを返す
     */
    private function _mb_trim($string)
    {
        static $chars = "[\\x0-\x20\x7f\xc2\xa0\xe3\x80\x80]";
        return preg_replace("/\A{$chars}++|{$chars}++\z/u", '', $string);
    }
    private function _detectEncoding($string)
    {
        // 文字コード判別順番
        $detectOrder = 'ASCII, JIS, UTF-8, CP51932, SJIS-win';
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // 文字コードを判定
        $encoding = mb_detect_encoding($string, $detectOrder, true);

        unset($string);
        if (!$encoding) {
            // 文字コードの自動判定に失敗
            throw new Exception('文字コードの判定に失敗しました。');
        }

        return $encoding;
    }
}