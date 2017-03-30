<?php

class UsersView extends ViewScript
{

    public function login()
    {
        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);

        // 基本
        $form->setAttribute('action', 'login');
        $form->setAttribute('method', 'POST');

        // ID
        $form->addElement(HtmlElement::create('label')->addText('ID')->setAttribute('for', 'id'));
        $form->addTextbox('id')->setAttribute('id', 'id');

        // パスワード
        $form->addElement(HtmlElement::create('label')->addText('パスワード')->setAttribute('for', 'password'));
        $form->addPassword('password')->setAttribute('id', 'password');

        // エンドポイント
        $endpoints = $this->endpoints;
        foreach ($endpoints as $endpoint) {
            $endpointsSelect[$endpoint['url']] = $endpoint['name'];
        }
        $form->addElement(HtmlElement::create('label')->addText('Zabbix API Endpoint')->setAttribute('for', 'endpoint'));
        $form->addSelect('endpoint', $endpointsSelect);

        // ボタン
        $form->addSubmit();

        // ビューに割当
        $this->form = $form->getHtml();
    }

}