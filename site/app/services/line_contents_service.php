<?php

/**
 * Line Contents
 */
class LineContentsService extends Service
{

    /**
     * 障害・工事情報を取得
     */
    public function get($where = null, $fields = null, $offset = 0, $limit = 100, $order = 'contents.id DESC')
    {
        $contents = $this->model('LineContents', 'contents');
        $contentFields = [
            'contents.id',
            'contents.vendor_id',
        ];

        $vendors = $this->model('LineVendors', 'vendors');
        $vendorFields = [
            'vendors.name AS vendor_name',
            'vendors.type AS vendor_type',
        ];

        $sel = $contents->select();

        if (isset($fields)) {
            $fields = array_merge($vendorFields, array_merge($contentFields, $fields));
        } else {
            $fields = array_merge($vendorFields, ['contents.*']);
        }
        $sel->fields($fields);

        if (isset($where)) {
            $sel->where($where);
        }

        $sel->joinInner($vendors, [
            'vendors.id = contents.vendor_id'
        ]);

        $sel->offset($offset);
        $sel->limit($limit);
        $sel->order($order);

        $rows = $sel->fetchAll();

        return $rows;
    }

}