<?php

class DataCheckView extends ViewScript
{

    // public function index()
    // {

    //     // コントローラで取得した hostgroup 情報を取得
    //     $new_hostgroups = $this->new_hostgroups;

    //     // *********** hostgroup 処理選択一覧画面の実装 ***********

    //     // $new_hostgroups に値があれば Table を作成
    //     if (!empty($new_hostgroups)) {

    //         $table = new HtmlTable();

    //         // Table への Header 追加
    //         $table->addHeader(array_keys(reset($new_hostgroups)));

    //         // Table への Data 追加
    //         $table->bindArray($new_hostgroups);

    //         // Table を View に追加
    //         $this->table = $table->getHtml();

    //     }


    //     // *********** hostgroup 選択プルダウンの実装 ***********

    //     // コントローラで取得した hostgroup の全情報を取得
    //     $zbx_hostgroups_full = $this->zbx_hostgroups_full;

    //     // HostGroup 選択 Form 作成
    //     $form = new HtmlForm();
    //     $form->setIsAutoLayout(false);
    //     $form->setAttribute('enctype', 'multipart/form-data');
    //     $form->setAttribute('method', 'GET');
    //     $form->addElement(HtmlElement::create('label')->addText('HostGroupを選択してください')->setAttribute('for', 'groupids'));
    //     $hostgroupList = [];
    //     foreach ($zbx_hostgroups_full as $hostgroup) {
    //         $hostgroupList[$hostgroup['groupid']] = $hostgroup['name'];
    //     }
    //     $form->addSelect('groupids', $hostgroupList);

    //     // 送信ボタン
    //     $form->addSubmit();

    //     // Form を View に追加
    //     $this->form = $form->getHtml();

    // }


    public function precheck()
    {

        // *********** history 検索で利用する URL 情報を View へ渡す ***********
        $url_BaseUrl = $this->request->getBaseUrl();
        $url_Path = $this->request->getPath();

        // *********** ヘルプ情報を取得する ***********
        $help = $this->_toolchipHelp('precheck');


        // 取得した情報を VIEW へ渡す
        $this->view->url_BaseUrl = $url_BaseUrl;
        $this->view->url_Path = $url_Path;
        $this->view->help = $help;

    }

    // public function precheck()
    // {

    //     // コントローラーで取得した query 情報を取得
    //     $query = $this->query;

    //     // コントローラで取得した host 情報を取得
    //     $new_hosts = $this->new_hosts;

    //     // *********** hostgroup 処理選択一覧画面の実装 ***********

    //     // $new_hosts に値があれば Table を作成
    //     if (!empty($new_hosts)) {

    //         $table = new HtmlTable();

    //         // Table への Header 追加
    //         $table->addHeader(array_keys(reset($new_hosts)));

    //         // Table への Data 追加
    //         $table->bindArray($new_hosts);

    //         // Table を View に追加
    //         $this->table = $table->getHtml();

    //     } else {

    //     // $new_hosts に値がなければ 'No Items Found.' と表示
    //         $this->table = 'No Items Found.';
    //     }
    // }

    public function hostcheck()
    {
        // *********** 配列から判定結果を取り出し HTML タグ付けした判定結果を取得 ***********
        // コントローラで取得した Host 名を取得
        $new_items = $this->new_items;

        $i = 0;
        foreach ($new_items as $val1) {
            $htmltag_decision = $this->_addDecisionTag($val1['decision']);
            $new_items[$i] = array_merge($new_items[$i], array('htmltag_decision'=>$htmltag_decision));
            $i = $i + 1;
        }

        // 取得した情報を VIEW へ渡す
        $this->view->new_items = $new_items;


        // *********** history 検索で利用する URL 情報を View へ渡す ***********
        $url_BaseUrl = $this->request->getBaseUrl();
        $url_Path = $this->request->getPath();

        // *********** ヘルプ情報を取得する ***********
        $help = $this->_toolchipHelp('hostcheck');

        // 取得した情報を VIEW へ渡す
        $this->view->url_BaseUrl = $url_BaseUrl;
        $this->view->url_Path = $url_Path;
        $this->view->help = $help;

    }

    private function _addDecisionTag($decision)
    {

        // *********** 判定結果に対して HTML タグ付けを行う ***********

        switch ($decision) {
            case 'NG(Empty)':
                return '<span class="pink">NG(Empty)</span>' ;
                break;
            case 'Delay':
                return '<span class="red">Delay</span>' ;
                break;
            case 'OK':
                return '<span class="green">OK</span>' ;
                break;
            case 'Not Received':
                return '<span class="orange">Not Received</span>' ;
                break;
            case 'Received':
                return '<span class="green">Received</span>' ;
                break;
        }

    }

    private function _toolchipHelp($help)
    {

        switch ($help) {
            case 'precheck':
                $result = <<<EOM
[出力結果について]<br>
　定期的にデータを取得するタイプの処理(0,1,3,4,5,6,8,9,10,11,12,13,14,15,16)<br>
　　データを一度も受信していない場合：NG(Empty)<br>
　　データを取得しているが遅延している：Delay<br>
　　データを取得していて遅延なし：OK<br>
<br>
　定期的なデータ取得でないタイプの処理(2,7,17)<br>
　　データを一度も受信していない場合：Not Received<br>
　　データを取得している：Received<br>
<br>
　　　　　[監視タイプ一覧]<br>
　　　　　0 - Zabbix agent; <br>
　　　　　1 - SNMPv1 agent; <br>
　　　　　2 - Zabbix trapper; <br>
　　　　　3 - simple check; <br>
　　　　　4 - SNMPv2 agent; <br>
　　　　　5 - Zabbix internal; <br>
　　　　　6 - SNMPv3 agent; <br>
　　　　　7 - Zabbix agent (active); <br>
　　　　　8 - Zabbix aggregate; <br>
　　　　　9 - web item; <br>
　　　　　10 - external check; <br>
　　　　　11 - database monitor; <br>
　　　　　12 - IPMI agent; <br>
　　　　　13 - SSH agent; <br>
　　　　　14 - TELNET agent; <br>
　　　　　15 - calculated; <br>
　　　　　16 - JMX agent; <br>
　　　　　17 - SNMP trap;<br>
<br>
[ヒストリ検索について]<br>
　初期状態では itemid をキーにして結果を取得しています。<br>
　しかし itemid では対象データを 24時間前しか確認しません。<br>
　（30時間前に対象データを受信していても結果を返さず、データを一度も<br>
　　受信していないこととして結果を返す仕様）<br>
<br>
　24時間前のデータも知りたい場合は "ヒストリ検索" ボタンをクリックください。<br>
　ヒストリ検索は item 1つに対して 1回 API を実行しますので処理に時間がかかります。<br>
　必要な場合は対象（host,item）を絞り込んでから実行することをお勧めします。<br>
<br>
[URL に直接指定して絞込み]<br>
　"precheck" では hostid で絞込みが可能です。<br>
<br>
　　(例) hostid 11111,22222 で絞り込む場合<br>
　　　　　<URLの最後尾に追加>&hostids[]=11111&hostids[]=22222<br>
EOM;
                break;

            case 'hostcheck':
                $result = <<<EOM
[出力結果について]<br>
　定期的にデータを取得するタイプの処理(0,1,3,4,5,6,8,9,10,11,12,13,14,15,16)<br>
　　データを一度も受信していない場合：NG(Empty)<br>
　　データを取得しているが遅延している：Delay<br>
　　データを取得していて遅延なし：OK<br>
<br>
　定期的なデータ取得でないタイプの処理(2,7,17)<br>
　　データを一度も受信していない場合：Not Received<br>
　　データを取得している：Received<br>
<br>
　　　　　[監視タイプ一覧]<br>
　　　　　0 - Zabbix agent; <br>
　　　　　1 - SNMPv1 agent; <br>
　　　　　2 - Zabbix trapper; <br>
　　　　　3 - simple check; <br>
　　　　　4 - SNMPv2 agent; <br>
　　　　　5 - Zabbix internal; <br>
　　　　　6 - SNMPv3 agent; <br>
　　　　　7 - Zabbix agent (active); <br>
　　　　　8 - Zabbix aggregate; <br>
　　　　　9 - web item; <br>
　　　　　10 - external check; <br>
　　　　　11 - database monitor; <br>
　　　　　12 - IPMI agent; <br>
　　　　　13 - SSH agent; <br>
　　　　　14 - TELNET agent; <br>
　　　　　15 - calculated; <br>
　　　　　16 - JMX agent; <br>
　　　　　17 - SNMP trap;<br>
<br>
[ヒストリ検索について]<br>
　初期状態では itemid をキーにして結果を取得しています。<br>
　しかし itemid では対象データを 24時間前しか確認しません。<br>
　（30時間前に対象データを受信していても結果を返さず、データを一度も<br>
　　受信していないこととして結果を返す仕様）<br>
<br>
　24時間前のデータも知りたい場合は "ヒストリ検索" ボタンをクリックください。<br>
　ヒストリ検索は item 1つに対して 1回 API を実行しますので処理に時間がかかります。<br>
　必要な場合は対象（host,item）を絞り込んでから実行することをお勧めします。<br>
<br>
[URL に直接指定して絞込み]<br>
　"hostcheck" では itemid で絞込みが可能です。<br>
<br>
　　(例) item 11111,22222 で絞り込む場合<br>
　　　　　<URLの最後尾に追加>&itemids[]=11111&itemids[]=22222<br>
EOM;
                break;
        }
        return $result;

    }

    public function configcheck()
    {
    }

}