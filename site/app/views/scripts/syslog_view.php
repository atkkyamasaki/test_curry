<?php

class SyslogView extends ViewScript
{

    public function index()
    {
        // ビュースクリプトを使うと明らかに遅い・・・
        $this->_sendSyslogForm();
        $this->_syslogTable();
        $this->_searchForm();
        $this->_saveForm();
    }

    private function _sendSyslogForm()
    {
        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);
        $form->setAttribute('method', 'POST');
        // 検索
        $form->addElement(HtmlElement::create('label')->addText('Syslog')->setAttribute('for', 'syslog'));
        $form->addTextbox('syslog')->setAttributes([
            'id' => 'syslog',
            'placeholder' => 'Syslog送信ワード',
        ]);
        // Severity
        $severities = [
            'LOG_EMERG',
            'LOG_ALERT',
            'LOG_CRIT',
            'LOG_ERR',
            'LOG_WARNING',
            'LOG_NOTICE',
            'LOG_INFO',
            'LOG_DEBUG',
        ];
        $form->addElement(HtmlElement::create('label')->addText('Severity')->setAttribute('for', 'severity'));
        $form->addSelect('severity', $severities);
        // ボタン
        $form->addSubmit();
        $this->send_syslog_form = $form->getHtml();
    }

    private function _searchForm()
    {
        // データ
        $keyword = $this->keyword;
        $severities = $this->severities;
        $severity = $this->severity;
        $limit = $this->limit;

        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);
        $form->setAttribute('method', 'GET');

        // 検索
        $form->addElement(HtmlElement::create('label')->addText('Search')->setAttribute('for', 'search'));
        if (isset($keyword)) {
            $form->addTextbox('search', $keyword)->setAttributes([
                'id' => 'search',
                'placeholder' => 'Syslog検索ワード',
            ]);
        } else {
            $form->addTextbox('search')->setAttributes([
                'id' => 'search',
                'placeholder' => 'Syslog検索ワード',
            ]);
        }

        // Severity
        $form->addElement(HtmlElement::create('label')->addText('Severity')->setAttribute('for', 'severity'));
        $form->addSelect('severity', array_merge($severities, [8 => '指定なし']), isset($severity) ? $severity : 8);

        // 表示件数
        $limits = [
            100 => 100,
            250 => 250,
            500 => 500,
            1000 => 1000,
            5000 => 5000,
            10000 => 10000,
        ];
        $form->addElement(HtmlElement::create('label')->addText('Display limit')->setAttribute('for', 'limit'));
        $form->addSelect('limit', $limits, isset($limit) ? $limit : 250);

        // ボタン
        $form->addSubmit();
        $this->search_form = $form->getHtml();
    }

    private function _saveForm()
    {
        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);
        $form->setAttribute('method', 'POST');
        // PUT
        $form->addHidden()->setAttributes([
            'name' => '_method',
            'value' => 'PUT',
        ]);
        // ボタン
        $form->addSubmit()->setAttributes([
            'value' => 'ダウンロード',
        ]);
        $this->save_form = $form->getHtml();
    }

    private function _syslogTable()
    {
        // データ
        $fields = $this->fields;
        $systemEvents = $this->system_events;

        // テーブル
        $table = new HtmlTable();
        $table->addHeader($fields);
        $table->bindArray($systemEvents);
        $this->syslog_table = $table->getHtml();
    }

}