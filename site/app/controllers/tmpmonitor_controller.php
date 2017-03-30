<?php

class TmpMonitorController extends RestController
{

    private $_resource;
    private $_method;
    private $_zabbixApi;

    public function index()
    {   

        // 事前定義
        // hostgroup 情報を API(get)  I
        $this->_resource = 'hostgroup';
        $this->_method = 'get';

        // API で host が登録されている hostgroup 情報を収集
        // (hostgroup 選択プルダウン用に取得)
        $zbx_hostgroups_in_host = $this->_zabbixApi->call($this->_resource . '.' . $this->_method, array('real_hosts' => '1'));

        // GET アクセスによる Hostgroup 情報があれば以下処理を実行
        

        if (!empty($this->query['groupids'])) {

            $mb_info = $this->_mbDecision($this->query['groupids']);
            // $mb_info = $this->_mbDecision('15');

            // 取得した情報を VIEW へ渡す
            $this->view->mb_info = $mb_info;

        } else {
            $zbx_hostgroups = null;
        }

        // 取得した情報を VIEW へ渡す
        $this->view->zbx_hostgroups_in_host = $zbx_hostgroups_in_host;

    }

    public function result()
    {   
        $mb_info = $this->_mbDecision($this->query['groupids'], $this->query['mbs']);

        // 取得した情報を VIEW へ渡す
        $this->view->mb_info = $mb_info;

    }

    private function _mbDecision($groupId, $mbName = NULL)
    {
        // 取得した Groupid より MonitorBox の情報を返す
        $results = [];        
        switch ($groupId) {
            
            case 25:
                $results = [];
                $mb0 = array(
                    'groupid' => '25',
                    'groupname' => 'FKSH_国立病院機構福島病院',
                    'mb' => 'mb0',
                    'mbname' => 'FKSH0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.10',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 30:
                $results = [];
                $mb0 = array(
                    'groupid' => '30',
                    'groupname' => 'SMCH_茨城西南医療センター病院',
                    'mb' => 'mb0',
                    'mbname' => 'SMCH0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.18',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 34:
                $results = [];
                $mb0 = array(
                    'groupid' => '34',
                    'groupname' => 'RIOC_株式会社リオSOHO兜町',
                    'mb' => 'mb0',
                    'mbname' => 'RIOC0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.34',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 36:
                $results = [];
                $mb0 = array(
                    'groupid' => '36',
                    'groupname' => 'KDZK_九段坂病院',
                    'mb' => 'mb0',
                    'mbname' => 'KDZK0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.42',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 37:
                $results = [];
                $mb0 = array(
                    'groupid' => '37',
                    'groupname' => 'AOMI_日本学生支援機構',
                    'mb' => 'mb0',
                    'mbname' => 'AOMI0',
                    'ip' => '150.87.1.201',
                    'natip' => '150.87.1.201',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 45:
                $results = [];
                $mb0 = array(
                    'groupid' => '45',
                    'groupname' => 'TMMH_都立松沢病院',
                    'mb' => 'mb0',
                    'mbname' => 'TMMH0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.50',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 48:
                $results = [];
                $mb0 = array(
                    'groupid' => '48',
                    'groupname' => 'KKHS_京都工学院高校',
                    'mb' => 'mb0',
                    'mbname' => 'KKHS0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.58',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 53:
                $results = [];
                $mb0 = array(
                    'groupid' => '53',
                    'groupname' => 'TTDC_トヨタテクニカルディベロップメント',
                    'mb' => 'mb0',
                    'mbname' => 'TTDC0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.74',
                );
                $mb1 = array(
                    'groupid' => '53',
                    'groupname' => 'TTDC_トヨタテクニカルディベロップメント',
                    'mb' => 'mb1',
                    'mbname' => 'TTDC1',
                    'ip' => '169.254.1.3',
                    'natip' => '10.0.1.75',
                );
                if (!empty($mbName)) {
                    if ($mbName === 'mb0') {
                        array_push($results, $mb0);
                    } elseif ($mbName === 'mb1') {
                        array_push($results, $mb1);
                    }

                } else {
                    array_push($results, $mb0);
                    array_push($results, $mb1);
                }
                return $results;
                break;

            case 54:
                $results = [];
                $mb0 = array(
                    'groupid' => '54',
                    'groupname' => 'KSWY_キッズウェイ',
                    'mb' => 'mb0',
                    'mbname' => 'KSWY0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.82',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 59:
                $results = [];
                $mb0 = array(
                    'groupid' => '59',
                    'groupname' => 'TSGH_高山整形外科病院',
                    'mb' => 'mb0',
                    'mbname' => 'TSGH0',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.1.90',
                );
                array_push($results, $mb0);
                return $results;
                break;

            case 15:
                $results = [];
                $mb0 = array(
                    'groupid' => '15',
                    'groupname' => 'ATKK_アライドテレシス-YCC CSテスト',
                    'mb' => 'mb0',
                    'mbname' => 'ycctest',
                    'ip' => '169.254.1.2',
                    'natip' => '10.0.0.26',
                );
                $mb1 = array(
                    'groupid' => '15',
                    'groupname' => 'ATKK_アライドテレシス-YCC CSテスト',
                    'mb' => 'mb1',
                    'mbname' => 'ycctest1',
                    'ip' => '169.254.1.3',
                    'natip' => '10.0.0.27',
                );
                $mb2 = array(
                    'groupid' => '15',
                    'groupname' => 'ATKK_アライドテレシス-YCC CSテスト',
                    'mb' => 'mb2',
                    'mbname' => 'ycctest2',
                    'ip' => '169.254.1.4',
                    'natip' => '10.0.0.28',
                );
                if (!empty($mbName)) {
                    if ($mbName === 'mb0') {
                        array_push($results, $mb0);
                    } elseif ($mbName === 'mb1') {
                        array_push($results, $mb1);
                    } elseif ($mbName === 'mb2') {
                        array_push($results, $mb2);
                    }
                } else {
                    array_push($results, $mb0);
                    array_push($results, $mb1);
                    array_push($results, $mb2);
                }
                return $results;
                break;

        }

    }



    public function allresult()
    {   
        $this->_commonAllresultSummary();
    }

    public function summary()
    {   
        $this->_commonAllresultSummary();
    }

    public function _commonAllresultSummary() {

        // 事前定義
        // 全 MonitorBox の情報を取得  I
        $mbs_info = [];
        array_push($mbs_info, $this->_mbDecision('25'));
        array_push($mbs_info, $this->_mbDecision('30'));
        array_push($mbs_info, $this->_mbDecision('34'));
        array_push($mbs_info, $this->_mbDecision('36'));
        array_push($mbs_info, $this->_mbDecision('37'));
        array_push($mbs_info, $this->_mbDecision('45'));
        array_push($mbs_info, $this->_mbDecision('48'));
        array_push($mbs_info, $this->_mbDecision('53'));
        array_push($mbs_info, $this->_mbDecision('54'));
        array_push($mbs_info, $this->_mbDecision('59'));
        array_push($mbs_info, $this->_mbDecision('15'));

        // 全 MonitorBox の NATIP 情報のみを取得
        $mbs_ip = [];
        foreach ($mbs_info as $val1) {
            foreach ($val1 as $key2 => $val2) {
                array_push($mbs_ip, $val2['natip']);
            }
        }

        // 取得した情報を VIEW へ渡す
        $this->view->mbs_info = $mbs_info;
        $this->view->mbs_ip = $mbs_ip;        
    }


    public function preProcess()
    {
        // ZabbixAPI を利用するために preProcess で呼び出し
        $this->_zabbixApi = $this->plugin->getZabbixApiInstance();
    }

    public function postProcess()
    {

    }

}