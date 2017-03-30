<?php

/**
 * CsvManipulator
 */
class CsvManipulator
{

    public function csvToArray($filename)
    {
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
        while ($row = fgetcsv($fp)) {
            if ($row === array(null)) {
                // 空行はスキップ
                continue;
            }
            // 前後のホワイトスペースを取り除く
//            var_dump($this->_mb_trim($row));
            $rows[] = $this->_mb_trim($row);
        }

        if (!feof($fp)) {
            // ファイルポインタが終端に達していなければエラー
            throw new Exception('CSVパースエラー。正しいファイルを選択して下さい。');
        }

        fclose($fp);
        return $rows;
    }

    public function arrayToCsv($array)
    {
        // tempストリームを開く
        $fp = fopen('php://temp', 'r+b');

        // 配列をストリームにcsvとして書き込む
        foreach ($array as $row) {
            fputcsv($fp, $row);
        }

        rewind($fp);

        // ストリームから変数に格納
        $csvString = stream_get_contents($fp);

        // 現在の文字コードを判定
        $fromEncoding = $this->_detectEncoding($csvString);

        // 文字コード変換を実行
        $toEncoding = 'SJIS-win';
        $encodedCsvString = mb_convert_encoding($csvString, $toEncoding, $fromEncoding);

        // 改行コードを変換
        $lineFeeds = [
            'Windows' => "\r\n",
            'Linux' => "\n",
            'MacOSX' => "\n",
            'MacOS' => "\r",
        ];
        $toLineFeed = $lineFeeds['Linux'];
        $convertedCsvString = str_replace(PHP_EOL, $toLineFeed, $encodedCsvString);

        return $convertedCsvString;
    }

    /**
     * エラーチェック
     */
    public function validate($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK: // 0
                // エラー無し
                break;
            case UPLOAD_ERR_INI_SIZE: // 1
            case UPLOAD_ERR_FORM_SIZE: //2
                // 許可サイズを超過（足りなければ.htaccessで追加）
                throw new Exception('ファイルサイズが大きすぎます。');
            case UPLOAD_ERR_NO_FILE: // 4
                // ファイル未選択
                throw new Exception('ファイルを選んでください。');
            default:
                throw new Exception('不明なエラー。');
        }
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