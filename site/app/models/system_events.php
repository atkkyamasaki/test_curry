<?php

/**
 * SystemEvents
 */
class SystemEvents extends Model
{

    /**
     * SystemEvents を取得
     */
    public function get($where = null, $fields = null, $keyword = null, $offset = 0, $limit = 250, $order = 'id DESC')
    {
        // DBのテーブル名をスネークケースにする必要がある system_events
        // 複数キーワード対応
        // or 検索対応
        $sel = $this->select();
        if (isset($fields)) {
            $sel->fields($fields);
        }
        if (isset($where)) {
            $sel->where($where);
        }
        if (isset($keyword)) {
            $sel->whereLike('Message', '%' . $keyword . '%');
        }
        $sel->offset($offset);
        $sel->limit($limit);
        $sel->order($order);
        $rows = $sel->fetchAll();
        return $rows;
    }

    /**
     * SystemEvents をカウント
     */
    public function count($keyword = null)
    {
        $sel = $this->select();
        if (isset($keyword)) {
            $sel->whereLike('Message', '%' . $keyword . '%');
        }
        $count = $sel->fetchCount();
        return $count;
    }

}