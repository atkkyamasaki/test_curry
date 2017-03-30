<?php

class MibsView extends ViewScript
{

    public function index()
    {
        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);

        // 基本
//        $form->setAttribute('action');
        $form->setAttribute('method', 'POST');

        // MIBファイル
        $mibs = $this->mibs;

        if ($mibs !== null) {
            $form->addElement(HtmlElement::create('label')->addText('Select mib(s)')->setAttribute('for', 'mibs'));
            $form->addCheckboxes('mibs[]', $mibs);
            // ボタン
            $form->addSubmit()->setAttribute(('value'), '変換');
        }

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