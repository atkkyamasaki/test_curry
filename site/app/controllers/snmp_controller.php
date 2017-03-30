<?php

class SnmpController extends RestController
{

    public function index()
    {
        // ページタイトル
        $this->view->setTitle('SNMP');

//        snmpwalk -c public -v 2c 127.0.0.1 ifDescr
//        snmptrap -v2c -c public 127.0.0.1 '' .1.3.6.1.4.1.10000 1 s "test trap"
//        define('MIBS_ALL_PATH', "/usr/share/snmp/mibs:/mnt/hgfs/htdocs/mibs");
        define('MIBS_ALL_PATH', "/mnt/hgfs/htdocs/mibs");
        define('ITEM_TYPE_SNMPV1', 1);
        define('ITEM_TYPE_SNMPV2C', 4);

        // MIB
        $files = $this->_search(MIBS_ALL_PATH);
//        var_dump($files);

        echo '<h2>MIBファイル</h2>';
        echo '<select>';
        if (!empty($files)) {
            foreach ($files as $file) {
                echo '<option>';
                echo $file;
                echo '</option>';
                echo '<br>';
            }
        } else {
            echo 'No MIB files.';
            echo '<br>';
        }
        echo '</select>';

        $oid = $this->get_oid_from_name('AGENTX-MIB::agentxMasterAgentXVer.0');
//        $oidTableValue = $this->get_table_value('public', '127.0.0.1', '2c', '.iso.org.dod.internet.private.enterprises.alliedTelesis.mibObject.brouterMib.atRouter.sysinfo');
        $oidValue = $this->get_oid_value('public', '127.0.0.1', '2c', '.iso.org.dod.internet.private.enterprises.alliedTelesis.mibObject.brouterMib.atRouter.sysinfo', 0);
        $oidContent = $this->get_oid_content('.iso.org.dod.internet.private.enterprises.alliedTelesis.mibObject.brouterMib.atRouter.sysinfo');
        $oidTree = $this->get_oid_tree('AT-SWITCH-MIB');

        echo '<br>';
        echo '$oid';
        var_dump($oid);
//        echo '$oidTableValue';
//        var_dump($oidTableValue);
        echo '$oidValue';
        var_dump($oidValue);
        echo '$oidContent';
        var_dump($oidContent);
        echo '$oidTree';
        var_dump($oidTree);
    }

    public function get_oid_from_name($name)
    {
        $name = preg_replace('/"/', '\\\\"', $name);
        if (preg_match("/'\w+'/", $name)) {
            $arr = preg_replace("/'/", '', preg_split("/\./", $name));
            $name = $arr[0];
            foreach (str_split($arr[1]) as $char) {
                $name = $name . "." . ord($char);
            }
        }
        $cmd = "snmptranslate -LE 1 -M " . MIBS_ALL_PATH . " -m ALL -On $name";
        $oid = exec("$cmd 2>&1", $results, $code);
        if ($code) {
            error(_('Function') . ": get_oid_from_name. " . _('Command') . ": $cmd. " . _('Error') . ": $code. " . _('Message') . ": " . join($results));
        }

        if (preg_match('/[0123456789\.]+/', $oid))
            return $oid;
        else
            return null;
    }

    public function get_table_value($community, $server_ip, $snmp_version, $oid)
    {
        // table view
        $rows = array();
        if ($server_ip == "") {
            $rows[0] = array(_('No host address provided'));
        } else {
            if ($snmp_version == ITEM_TYPE_SNMPV1) {
                $ver = '1';
            } else {
                $ver = '2c';
            }

            $results = array();
            $cmd = "snmptable -v $ver -c $community -M " . MIBS_ALL_PATH . " -Ci -Ch -Cf \",\" -m ALL $server_ip $oid";
            exec("$cmd 2>&1", $results, $code);
            if ($code) {
                error(_('Function') . ": get_table_value. " . _('Command') . ": $cmd. " . _('Error') . ": $code. " . _('Message') . ": " . join($results));
            }
            $headers = explode(",", $results[0]);
            unset($results);

            $cmd = "snmptable -v $ver -c $community -M " . MIBS_ALL_PATH . " -Ci -CH -Cf \",\" -m ALL $server_ip $oid";
            exec("$cmd 2>&1", $results, $code);
            if ($code) {
                error(_('Function') . ": get_table_value. " . _('Command') . ": $cmd. " . _('Error') . ": $code. " . _('Message') . ": " . join($results));
            }
            foreach ($results as $line) {
                $row = explode(",", $line);
                array_push($rows, $row);
            }
            unset($results);
        }

        $value = array('ret' => 1, 'headers' => $headers, 'rows' => $rows);
        return ($value);
    }

    public function get_oid_value($community, $server_ip, $snmp_version, $oid, $idx)
    {
        if (!$server_ip) {
            $row = array(_('No host address provided'), '', '');
            $value = array('ret' => 0, 'row' => $row);
            return ($value);
        }

        if ($snmp_version == ITEM_TYPE_SNMPV1) {
            $ver = '1';
        } else {
            $ver = '2c';
        }

        // idx is number or string thank danrog
        if (preg_match('/^[0-9]+$/', $idx)) {
            $cmd = "snmpget -v $ver -c $community -M " . MIBS_ALL_PATH . " -m ALL $server_ip $oid.$idx";
        } else {
            $cmd = "snmpget -v $ver -c $community -M " . MIBS_ALL_PATH . " -m ALL $server_ip $oid.\"" . $idx . "\"";
        }
        exec($cmd, $results, $code);
        if ($code) {
            error(_('Function') . ": get_oid_value. " . _('Command') . ": $cmd. " . _('Error') . ": $code. " . _('Message') . ": " . join($results));
        }

        //exampe: IP-MIB::ipOutRequests.0 = Counter32: 12303729
        if (preg_match('/^(\S+) = (\S+): (.+)$/i', $results[0], $matches)) { // full information
            $row = array($matches[1], $matches[2], $matches[3]);
        } else if (preg_match('/^(\S+) = (\S+):$/i', $results[0], $matches)) { //no value
            $row = array($matches[1], $matches[2], '');
        } else if (preg_match('/^(\S+) = (.+)$/i', $results[0], $matches)) { //no type
            $row = array($matches[1], '', $matches[2]);
        } else // error
            $row = array(join(' ', $results), '', '');
        $value = array('ret' => 0, 'row' => $row);
        return ($value);
    }

    function get_oid_content($oid)
    {
        $cmd = "snmptranslate -Td -OS -M " . MIBS_ALL_PATH . " -m ALL $oid";
        exec($cmd, $results, $code);
        if ($code) {
            error(_('Function') . ": get_oid_content. " . _('Command') . ": $cmd. " . _('Error') . ": $code. " . _('Message') . ": " . join($results));
        }

//        var_dump($results);

        $content = implode("<br>", $results);
        return ($content);
    }

    //Get oid tree per mib 
    public function get_oid_tree($mib)
    {
        $cmd = "snmptranslate -Ts -M " . MIBS_ALL_PATH . " -m $mib";
        exec($cmd, $results, $code);
        if ($code) {
            error(_('Function') . ": get_oid_tree. " . _('Command') . ": $cmd. " . _('Error') . ": $code. " . _('Message') . ": " . join($results));
        }

        $oid_tree = $this->explodeTree($mib, $results);
        return $oid_tree;
    }

    public function explodeTree($mib, $array, $delimiter = '.')
    {
        if (!is_array($array))
            return false;
        $splitRE = '/' . preg_quote($delimiter, '/') . '/';
        $returnArr['attr']['id'] = '';
        $returnArr['data'] = $mib;
        $returnArr['attr']['rel'] = 'globe';
        $returnArr['children'] = array(array('attr' => array('id' => '.iso'), 'data' => 'iso'), array('attr' => array('id' => '.ccitt'), 'data' => 'ccitt'));

        foreach ($array as $key) {
            // Get parent parts and the current leaf
            $parts = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
            $leaf = array_pop($parts);
            $parentArr = &$returnArr;

            foreach ($parts as $part) {
                $child_id = $parentArr['attr']['id'] . '.' . $part;
                if (!isset($parentArr['children']))
                    $parentArr['children'] = array();

                for ($i = 0; $i < count($parentArr['children']); $i++) {
                    if ($parentArr['children'][$i]['attr']['id'] == $child_id) {
                        break;
                    }
                }

                if (!isset($parentArr['children'][$i])) {
                    echo $child_id . " " . $leaf . " " . $key;
                    exit();
                }

                $parentArr = &$parentArr['children'][$i];
            }
            if (!isset($parentArr['children'])) {
                $parentArr['children'] = array();
            }
            $i = count($parentArr['children']);
            $parentArr['children'][$i]['attr']['id'] = $key;
            $parentArr['children'][$i]['data'] = $leaf;
            if (preg_match('/^\w+Table$/', $leaf)) {
                $parentArr['children'][$i]['attr']['rel'] = 'table';
            }
        }

        return $returnArr;
    }

    private function _search($directory)
    {
        // 保存済ファイルの検索
        $handle = opendir($directory);
        if ($handle) {
            $filename = null;
            // ディレクトリを捜査
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $filename[] = $file;
                }
            }
            closedir($handle);
        }
        return $filename;
    }

}