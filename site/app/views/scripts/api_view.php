<?php

class ApiView extends ViewScript
{

    public function index()
    {
        // レスポンス
        $responses = $this->responses;

        // TODO アイテムがなかった場合（proxyなどで確認可能）
        if (!empty($responses)) {
            // テーブル
            $table = new HtmlTable();

            // ヘッダ行数が多い場合の一時的回避
            // ヘッダ数が多かったら $resuponses をそのまま view に割り当てる
            try {
                // ヘッダを追加
                $table->addHeader(array_keys(reset($responses)));
                // データを追加
                $table->bindArray($responses);
            } catch (Exception $e) {
                $this->view->debug_message = $e->getMessage();
                $this->view->debug = $responses;
            }

            // テーブルをビューに追加
            $this->table = $table->getHtml();
        } else {
            $this->table = 'No Items Found.';
        }

        // ファイル送信フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('method', 'POST');
        // ファイル
        $form->addElement(HtmlElement::create('label')->addText('CSV File')->setAttribute('for', 'csv'));
        $form->addFile('csv')->setAttribute('id', 'csv');
        // メソッド
        $form->addElement(HtmlElement::create('label')->addText('Zabbix API Method')->setAttribute('for', '_method'));
        $methods = [
            'GET' => 'GET（get 表示）',
            'POST' => 'POST（create 作成）',
            'PUT' => 'PUT（massadd 追加）',
            'DELETE' => 'DELETE（delete 削除）',
        ];
        $form->addSelect('_method', $methods)->setAttribute('id', '_method');
        // ボタン
        $form->addSubmit();
        // フォームをビューに追加
        $this->form = $form->getHtml();
    }

    public function post()
    {
        // ↓一度変数に格納しないと isset() で確認できない
        // ヘッダ行
        $headerRows = $this->header_rows;
        // データ行
        $dataRows = $this->data_rows;
        // レスポンス
        $responses = $this->responses;

        if (isset($headerRows) && isset($dataRows)) {
            // フォーム
            $form = new HtmlForm();
            $form->setIsAutoLayout(false);
            $form->setAttribute('method', 'POST');
            $form->addHidden('csv', $this->csv);
            //        $csvManipulator = new CsvManipulator();
            //        $csvArray = $csvManipulator->getArray($this->csv);
            //        var_dump($csvArray);
            $form->addSubmit();
            $this->form = $form->getHtml();

            // テーブル
            $table = new HtmlTable();
            // ベースヘッダ
            $table->addHeader(reset($headerRows));
            if (count($headerRows) === 2) {
                // ヘッダが2行の場合サブヘッダも追加
                $table->addHeader(next($headerRows));
            }
            // データを配列で追加
            $table->bindArray($dataRows);
            // フォームをビューに追加
            $this->table = $table->getHtml();
        } elseif (isset($responses)) {
            // 結果
            $pre = new HtmlElement('pre');
            $pre->addText(var_export($responses, true));
            $this->pre = $pre->getHtml();
        }
    }

    public function put()
    {
        // ↓一度変数に格納しないと isset() で確認できない
        // ヘッダ行
        $headerRows = $this->header_rows;
        // データ行
        $dataRows = $this->data_rows;
        // レスポンス
        $responses = $this->responses;

        if (isset($headerRows) && isset($dataRows)) {
            // フォーム
            $form = new HtmlForm();
            $form->setIsAutoLayout(false);
            $form->setAttribute('method', 'POST');
            $form->addHidden('_method', 'PUT');
            $form->addHidden('csv', $this->csv);
            //        $csvManipulator = new CsvManipulator();
            //        $csvArray = $csvManipulator->getArray($this->csv);
            //        var_dump($csvArray);
            $form->addSubmit();
            $this->form = $form->getHtml();

            // テーブル
            $table = new HtmlTable();
            // ベースヘッダ
            $table->addHeader(reset($headerRows));
            if (count($headerRows) === 2) {
                // ヘッダが2行の場合サブヘッダも追加
                $table->addHeader(next($headerRows));
            }
            // データを配列で追加
            $table->bindArray($dataRows);
            // フォームをビューに追加
            $this->table = $table->getHtml();
        } elseif (isset($responses)) {
            // 結果
            $pre = new HtmlElement('pre');
            $pre->addText(var_export($responses, true));
            $this->pre = $pre->getHtml();
        }
    }

    public function delete()
    {
        
    }

}