<?php

/**
 * Helper
 */
class Helper
{
    /**
     * 
     */
    private $_versions = [
        'html5shiv.min.js' => '3.7.3',
        'jquery.min.js' => '2.2.3',
        'jquery.cookie.min.js' => '1.4.1',
    ];

    /**
     * 
     */
    public function getFileQuery($filename, $type)
    {
        if (isset($this->_versions[$filename])) {
            $query = $this->_versions[$filename];
        } else {
            $filePath = sprintf('%s/' . $type . '/%s', PathManager::getHtdocsDirectory(), $filename);
            if (file_exists($filePath)) {
                $query = date('YmdHis', filemtime($filePath));
            }
        }
        return '?v=' . $query;
    }

    /**
     * URL を生成
     *
     * @param array|string $url
     * @param array $params
     * @return string
     */
    public function createUrl($url, $params = null)
    {
        // URL が配列だったらスラッシュ区切りに直す
        if (is_array($url)) {
            $url = implode('/', $url);
        }
        // URL の最初がスラッシュじゃなければ付与
        if (substr($url, 0, 4) !== 'http' && substr($url, 0, 5) !== 'https' && substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        // URL の最後がスラッシュではなく最後のスラッシュ以降に.がなければファイルではないとみなして最後のスラッシュを付与
//        if (substr($url, -1, 1) !== '/' && preg_match('|\.[^/\.]+$|', $url) !== 1) {
//            $url = $url . '/';
//        }
        // URL パラメータを生成
        // if ($params !== null) {
        //     $i = 0;
        //     foreach ($params as $key => $value) {
        //         if ($i === 0) {
        //             $params = '?' . $key . '=' . $value;
        //         } else {
        //             $params .= '&' . $key . '=' . $value;
        //         }
        //         $i++;
        //     }
        // }

        // 取り出した $value に配列が入っていた場合の対策を追記
        if ($params !== null) {
            $i = 0;
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        if ($i === 0) {
                            $params = '?' . $key . '[]' . '=' . $value2;
                        } else {
                            $params .= '&' . $key . '[]' .'=' . $value2;
                        }
                        $i++;
                    }
                } else {
                    if ($i === 0) {
                        $params = '?' . $key . '=' . $value;
                    } else {
                        $params .= '&' . $key . '=' . $value;
                    }
                    $i++;
                }
            }
        }

        return $this->escape($url . $params);
    }

    /**
     * フル URL を生成
     *
     * @param string $domain
     * @param array|sring $url
     * @param array $params
     * @return string
     */
    public function createFullUrl($domain, $url, $params = null)
    {
        if (substr($domain, -1, 1) === '/') {
            $domain = rtrim($domain, '/');
        }
        // URL が配列だったらスラッシュ区切りに直す
        if (is_array($url)) {
            $url = implode('/', $url);
        }
        // URL の最初がスラッシュじゃなければ付与
        if (substr($url, 0, 4) !== 'http' && substr($url, 0, 5) !== 'https' && substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        // URL の最後がスラッシュではなく最後のスラッシュ以降に.がなければファイルではないとみなして最後のスラッシュを付与
//        if (substr($url, -1, 1) !== '/' && preg_match('|\.[^/\.]+$|', $url) !== 1) {
//            $url = $url . '/';
//        }
        // URL パラメータを生成
        if ($params !== null) {
            $i = 0;
            foreach ($params as $key => $value) {
                if ($i === 0) {
                    $params = '?' . $key . '=' . $value;
                } else {
                    $params .= '&' . $key . '=' . $value;
                }
                $i++;
            }
        }

        return $this->escape($domain . $url . $params);
    }

    /**
     * 相対日付を整形
     *
     * @param string $date 2015-01-01 00:00:00 形式
     * @param boolean $minutes 分まで変換するか
     * @return string
     */
    public function formatRelativeTime($date, $minutes = true)
    {
        $gap = time() - strtotime($date);
        if ($gap < 5) {
            return '5秒前';
        } else if ($gap < 10) {
            return '10秒前';
        } else if ($gap < 20) {
            return '20秒前';
        } else if ($gap < 30) {
            return '30秒前';
        } else if ($gap < 60) {
            return '1分前';
        }
        $gap = round($gap / 60);
        if ($gap < 60) {
            return $gap . '分前';
        }
        $gap = round($gap / 60);
        if ($gap < 24) {
            return $gap . '時間前';
        }
        if (!$minutes) {
            return date('Y年n月j日', strtotime($date));
        }
        return date('Y年n月j日 H:i', strtotime($date));
    }

    /**
     * 変数エスケープ
     *
     * @param array|string $string
     * @return type
     */
    public function escape($string)
    {
        if (is_array($string)) {
            return array_map('escape', $string);
        } else {
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * 現在のURL情報を取得
     *
     * @return Url
     */
    public function getFullUrlNow()
    {

        return (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    }




}