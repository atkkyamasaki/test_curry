<?php

class ScriptsController extends RestController
{

    public function index()
    {
        // ページタイトル
        $this->view->setTitle('Scripts');
    }

}