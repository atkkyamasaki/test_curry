<?php

/**
 * Paginator
 */
class Paginator
{
    /**
     * パージネーター
     *
     * @var array
     */
    private $_paginator;

    /**
     * ページャーの表示数
     *
     * @var integer
     */
    private $_display = 5;

    /**
     * URLクエリ
     *
     * @var array
     */
    private $_query = null;

    /**
     * ページキー
     *
     * @var string
     */
    private $_pageKey = 'page';

    /**
     * パージネイト
     *
     * @param integer $currentPage 現在のページ
     * @param integer $resultsCount アイテムの合計数
     * @param integer $resultsPerPage 1件あたりの表示数
     * @return void
     */
    public function paginate($currentPage, $resultsCount, $resultsPerPage)
    {
        if ($resultsCount === 0) {
            return;
        }
        // 現在のページが null か数字じゃなかったら1を設定しておく
        if ($currentPage === null || !is_numeric($currentPage)) {
            $currentPage = 1;
        }
        // 前のページと次のページへのリンクを求める
        $previousPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        // 最大ページ数を求める
        $maxPage = ceil($resultsCount / $resultsPerPage);
        // 仮の始点
        $_start = ($currentPage - floor($this->_display / 2) > 0) ? ($currentPage - floor($this->_display / 2)) : 1;
        // 終点
        $end = ($_start > 1) ? ($currentPage + floor($this->_display / 2)) : $this->_display;
        // 始点を再計算
        $start = ($maxPage < $end) ? $_start - ($end - $maxPage) : $_start;
        // 前のページ
        $previousUrl = null;
        if ($currentPage !== 1) {
            // 前のページが存在すればURLを返す
            $previousUrl = $previousPage;
        }
        // 最初のページへのリンク
        $firstUrl = null;
        $firstDelimiter = false;
        if ($start >= floor($this->_display / 2)) {
            // 最初のページへのURl
            $firstUrl = 1;
            // 最初のページへが離れたら ... を表示
            if ($start > floor($this->_display / 2)) {
                $firstDelimiter = true;
            }
        }
        // ページリンクを取得
        $pages = [];
        for ($i = $start; $i <= $end; $i++) {
            if ($i <= $maxPage && $i > 0) {
                $pages[] = [
                    'page' => (int) $i,
                    'current' => ((int) $currentPage === (int) $i ? true : false),
                ];
            }
        }
        // 最後のページへのリンク
        $lastUrl = null;
        $lastDelimiter = false;
        if ($maxPage > $end) {
            // 最初のページへのURL
            $lastUrl = $maxPage;
            if ($maxPage - 1 > $end) {
                // 最後のページへが離れたら ... を表示
                $lastDelimiter = true;
            }
        }
        // 次ののページ
        $nextUrl = null;
        if ($currentPage < $maxPage) {
            // 次のページが存在すればURLを返す
            $nextUrl = $nextPage;
        }
        // URLパラメータ
        $query = null;
        if ($this->_query !== null) {
            foreach ($this->_query as $key => $value) {
                $query .= '&' . $key . '=' . $value;
            }
        }
        // オフセットを求める
        $offset = ($currentPage - 1) * $resultsPerPage;

        // 現在のページの表示数を求める
        if ((int) $currentPage === (int) $maxPage) {
            // 最後のページ
            $currentDisplay = $resultsCount - $offset;
        } else {
            // 最後以外のページ
            $currentDisplay = $resultsPerPage;
        }
        $this->_paginator = [
            'current_page' => (int) $currentPage,
            'current_display' => (int) $currentDisplay,
            'results_count' => (int) $resultsCount,
            'results_per_page' => (int) $resultsPerPage,
            'max_page' => (int) $maxPage,
            'previous_url' => (int) $previousUrl,
            'first_url' => (int) $firstUrl,
            'first_delimiter' => (bool) $firstDelimiter,
            'next_url' => (int) $nextUrl,
            'last_url' => (int) $lastUrl,
            'last_delimiter' => (bool) $lastDelimiter,
            'pages' => (array) $pages,
            'query' => (string) $query,
            'offset' => (int) $offset,
        ];
    }

    /**
     * パージネーターを取得
     *
     * @return array
     */
    public function getPaginator()
    {
        return $this->_paginator;
    }

    /**
     *
     * 1ページあたりのアイテム表示数を取得
     *
     * @return integer
     */
    public function getResultsPerPage()
    {
        return $this->_paginator['results_per_page'];
    }

    /**
     *
     * データオフセットを取得
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->_paginator['offset'];
    }
    /*
     * パージネーターの表示数を設定
     *
     * @param integer $display パージネーターの表示数
     */

    public function setDisplay($display)
    {
        $this->_display = $display;
    }
    /*
     * URLクエリを設定
     *
     * @param string $query URLクエリ
     */

    public function setQuery($query)
    {
        // ページキーがあったら取り除く
        if (isset($this->_pageKey)) {
            unset($query[$this->_pageKey]);
        }
        $this->_query = $query;
    }

}