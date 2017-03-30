<?php

class PingView extends ViewScript
{

    public function index()
    {
        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);

        // 基本
//        $form->setAttribute('action');
        $form->setAttribute('method', 'POST');

        // モード
        $mode = [
            'ping死活監視対象ホスト',
        ];
        $form->addElement(HtmlElement::create('label')->addText('Mode')->setAttribute('for', 'mode'));
        $form->addCheckboxes('mode', $mode);

        // 取得するホストのステータス
        $status = [
            'すべてのホスト',
            '現在有効なホストのみ',
            '現在無効なホストのみ',
        ];
        $form->addElement(HtmlElement::create('label')->addText('Status')->setAttribute('for', 'status'));
        $form->addSelect('status', $status);

        // ホストグループ
        $hostgroups = $this->hostgroups;
        $form->addElement(HtmlElement::create('label')->addText('Select hostgroup(s)')->setAttribute('for', 'groupids'));
        $hostgroupList = [];
        foreach ($hostgroups as $hostgroup) {
            $hostgroupList[$hostgroup['groupid']] = $hostgroup['name'];
        }
        $form->addCheckboxes('groupids[]', $hostgroupList);

        // ボタン
        $form->addSubmit();

        // ビューに割当
        $this->form = $form->getHtml();
    }

    public function post()
    {
        
    }

    public function put()
    {
        
    }

    public function delete()
    {
        
    }

}