<?php

class ExchangeView extends ViewScript
{
    public function index()
    {
        // フォーム
        $form = new HtmlForm();
        $form->setIsAutoLayout(false);
        $form->setAttribute('method', 'POST');

        // エンドポイント
        $endpoints = $this->endpoints;
        foreach ($endpoints as $endpoint) {
            // 現在のエンドポイント
            if ($endpoint['url'] === $this->endpoint) {
                $currentEndpointSelectOptions[$endpoint['url']] = $endpoint['name'];
            }
            // エンドポイント一覧
            if ($endpoint['url'] !== $this->endpoint) {
                // 現在のエンドポイントは排除する
                $endpointsSelectOptions[$endpoint['url']] = $endpoint['name'];
            }
        }

        // ローディングアイコン
        $loadingIcon = new HtmlElement('i');
        $loadingIcon->addClass('fa fa-spinner fa-spin')->setAttribute('aria-hidden', 'true')->addStyle('display', 'none');
        
        // 現在のエンドポイントURLをhiddenに埋め込んでおく
        $form->addHidden('endpoint-origin', $this->endpoint);
        
        // Zabbix API Endpoint
        $form->addElement(HtmlElement::create('h3')->addText('STEP 1 Select Zabbix API Endpoint'));
        $form->addElement(HtmlElement::create('p')->addText('インポート/エクスポートしたいサーバを選択。（Source 送信元 → Destination 受信先 は [Swap Endpoint] クリックで入替え）'));
                
        // 現在のエンドポイント
        $currentEndpointDiv = new HtmlElement('div');
        $currentEndpointDiv->addClass('endpoint endpoint-origin')->addClass('is-connected');

        // 現在のエンドポイント詳細
        $currentEndpointText = new HtmlElement('p');
        $currentEndpointText->addClass('detail');
        $currentEndpointType = new HtmlElement('span');
        $currentEndpointType->addText('Source')->addClass('location');
        $currentEndpointUrl = new HtmlElement('span');
        $currentEndpointUrl->addText($this->endpoint)->addClass('url');
        $currentEndpointText->addElements([$currentEndpointType, $currentEndpointUrl]);
        $currentEndpointDiv->addElement($currentEndpointText);

        // 現在のエンドポイントのセレクトボックス
        $currentEndpointSelect = $form->createSelect('source', $currentEndpointSelectOptions)->addStyle('width', '400px');
        $currentEndpointDiv->addElement($currentEndpointSelect);

        // 現在のエンドポイントのログイン状態
        $currentEndpointLoginState = new HtmlElement('span');
        $currentEndpointLoginState->addText('接続中')->addClass('state');
        $currentEndpointDiv->addElement($currentEndpointLoginState);

        $form->addElement($currentEndpointDiv);

        // 入替えテキスト
        $swapText = new HtmlElement('p');
        $swapText->addText('↓ Swap Endpoint');
        $swapText->addClass('exchange-swap js-exchange-swap');
        $form->addElement($swapText);

        // その他のエンドポイント一覧
        array_unshift($endpointsSelectOptions, 'エンドポイントを選択');
        $endpointDiv = new HtmlElement('div');
        $endpointDiv->addClass('endpoint endpoint-other');
        
        // その他のエンドポイントのセレクトボックス
        $endpointSelect = $form->createSelect('destination', $endpointsSelectOptions)->addStyle('width', '400px');

        // その他のエンドポイント詳細
        $endpointText = new HtmlElement('p');
        $endpointText->addClass('detail');
        $endpointType = new HtmlElement('span');
        $endpointType->addText('Destination')->addClass('location');
        $endpointUrl = new HtmlElement('span');
        $endpointUrl->addText('Select the other endpoint')->addClass('url');
        $endpointText->addElements([$endpointType, $endpointUrl]);
        $endpointDiv->addElement($endpointText);

        // その他のエンドポイントのログイン状態
        $endpointLoginState = new HtmlElement('span');
        $endpointLoginState->addText('未接続')->addClass('state');
        $endpointDiv->addElement($endpointSelect);
        $endpointDiv->addElement($endpointLoginState);

        // ID/パスワード
        $loginDiv = new HtmlElement('div');
        $loginDiv->addClass('login')->addStyle('display', 'none');
        $idInput = $form->createTextbox('id')->addClass('zabbix-id')->setAttribute('placeholder', 'ID');
        $passwordInput = $form->createPassword('password')->addClass('zabbix-password')->setAttribute('placeholder', 'Password');
        $loginSubmit = $form->createSubmit('login', 'Login')->addClass('zabbix-login');
        $loginDiv->addElements([$idInput, $passwordInput, $loginSubmit, $loadingIcon]);
        $endpointDiv->addElement($loginDiv);

        // エンドポイント結果フィールド
        $endpointResult = new HtmlElement('div');
        $endpointResult->addClass('result js-endpoint-result');
        $endpointDiv->addElement($endpointResult);
        $form->addElement($endpointDiv);

        // リソース
        $form->addElement(HtmlElement::create('h3')->addText('STEP 2 Select Resource and Items'));
        $form->addElement(HtmlElement::create('p')->addText('エクスポートしたいリソースの選択。'));
        $resource = [
            0 => 'リソースを選択',
            'template' => 'テンプレート',
        ];
        $form->addSelect('resource', $resource)->addClass('resource')->addStyle('display', 'none');
        $form->addElement($loadingIcon);

        // ソースのホストグループ
        $form->addSelect('hostgroup-source', ['ホストグループを選択'])->addClass('hostgroup hostgroup-source')->addStyle('display', 'none');
        
        // リソース読み込みボタン
        $form->addSubmit('load-resource', 'Load Resource')->addStyle('display', 'none');
        $form->addElement($loadingIcon);

        // リソース取得結果フィールド
        $resourceResult = new HtmlElement('div');
        $resourceResult->addClass('result js-resource-result');
        $form->addElement($resourceResult);

        // 実行
        $form->addElement(HtmlElement::create('h3')->addText('STEP 3 Execute'));
        $form->addElement(HtmlElement::create('p')->addText('インポート先のホストグループを選択して実行。'));
        // デスティネーションのホストグループ
        $form->addSelect('hostgroup-destination', ['ホストグループを選択'])->addClass('hostgroup hostgroup-destination')->addStyle('display', 'none');
        $form->addElement($loadingIcon);
        // 実行ボタン
        $form->addSubmit('execute-exchange', 'Execute Exchange')->addStyle('display', 'none');

        // 実行結果取得結果フィールド
        $executeResult = new HtmlElement('div');
        $executeResult->addClass('result js-execute-result');
        $form->addElement($executeResult);
        
        // フォームをビューに追加
        $this->form = $form->getHtml();
    }
}