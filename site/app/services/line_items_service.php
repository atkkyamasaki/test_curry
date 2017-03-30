<?php

/**
 * Line Items
 */
class LineItemsService extends Service
{

    /**
     * 監視アイテムを取得
     */
    public function get($where = null, $fields = null, $offset = 0, $limit = 100, $order = 'items.id DESC')
    {
        $items = $this->model('LineItems', 'items');
        $itemFields = [
            'items.id',
            'items.name',
        ];

        $vendors = $this->model('LineVendors', 'vendors');
        $vendorFields = [
            'vendors.name AS vendor_name',
            'vendors.type AS vendor_type',
        ];

        $sel = $items->select();

        if (isset($fields)) {
            $fields = array_merge($vendorFields, array_merge($itemFields, $fields));
        } else {
            $fields = array_merge($vendorFields, ['items.*']);
        }
        $sel->fields($fields);

        if (isset($where)) {
            $sel->where($where);
        }

        $sel->joinInner($vendors, [
            'vendors.id = items.vendor_id'
        ]);

        $sel->offset($offset);
        $sel->limit($limit);
        $sel->order($order);

        $rows = $sel->fetchAll();

        return $rows;
    }

}