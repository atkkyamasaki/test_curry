<?php

class ValuemappingService extends Service
{
    /**
     * Export ValueMappings
     */
    public function export()
    {
        // Valuemap
        $valuemaps = $this->model('Valuemaps')->select()->fetchAll();
        $array = [];
        $i = 0;
        // 親となる Valuemap の配下に Mappings を配置
        foreach ($valuemaps as $valuemap) {
            $array[$i] = [
                'valuemapid' => $valuemap['valuemapid'],
                'name' => $valuemap['name'],
                // Mappings
                'mappings' => $this->model('Mappings')->select()->where('valuemapid', $valuemap['valuemapid'])->fetchAll(),
            ];
            $i++;
        }
        // Json に変換
        $json = json_encode($array);
        // 一応返す
        return $json;
    }

    /**
     * Import ValueMappings
     */
    public function import($json)
    {
        // Json から 配列に変換
        $array = json_decode($json, true);

        if ($array === null) {
            return;
        }

        // DB から最新の valuemapid を取得して + 1 しておく
        $newValuemapid = max($this->model('Valuemaps')->select()->fetchAll())['valuemapid'] + 1;

        // DB から最新の mappingid を取得して + 1 しておく
        $newMappingid = max($this->model('Mappings')->select()->fetchAll())['mappingid'] + 1;

        echo '<p><a href="/valuemappings">戻る</a></p>';

        // インポート
        foreach ($array as $valuemapping) {
            // 初期化
            $existingValuemapid = null;
            $isValuemapCreated = false;

            // Valuemaps をインポート
            if (!$this->model('Valuemaps')->findByName($valuemapping['name'])) {
                // 同名の valuemap がなければ新規作成
                $valuemappingFields = [
                    'valuemapid' => $newValuemapid,
                    'name' => $valuemapping['name'],
                ];
                $this->model('Valuemaps')->insert()->values($valuemappingFields)->execute();
                echo '<hr>';
                echo '<p style="color:blue">valuemaps: [<strong>' . $valuemapping['name'] . '</strong>] was created.</p>';
                // フラグ
                $isValuemapCreated = true;
            } else {
                // 同名の valuemap があったら既存の valuemapid を取得
                $existingValuemapid = $this->model('Valuemaps')->findByName($valuemapping['name'])['valuemapid'];
                echo '<hr>';
                echo '<p style="color:gray">valuemaps: [' . $valuemapping['name'] . '] already exists.</p>';
            }

            // 親となる valuemapid
            // 新規作成していれば新規の $newValuemapid 既にあれば $existingValuemapid
            $parentValuemapid = isset($existingValuemapid) ? $existingValuemapid : $newValuemapid;

            // Mappings をインポート
            foreach ($valuemapping['mappings'] as $mapping) {
                if (!$this->model('Mappings')->findByValueAndNewvalueAndValuemapid($mapping['value'], $mapping['newvalue'], $parentValuemapid)) {
                    // 同じ組み合わせの valuemap (value -> newvalue) がなければ新規作成
                    $mappingFields = [
                        'mappingid' => $newMappingid,
                        'valuemapid' => $parentValuemapid,
                        'value' => $mapping['value'],
                        'newvalue' => $mapping['newvalue'],
                    ];
                    $this->model('Mappings')->insert()->values($mappingFields)->execute();
                    echo '<hr>';
                    echo '<p style="color:blue"> ┗  mappings: [<strong>' . $mapping['value'] . ' =>' . $mapping['newvalue'] . '</strong>] was created at valuemaps: [<strong>' . $valuemapping['name'] . '</strong>]</p>';
                    $newMappingid++;
                }
            }

            // 新しい valuemap が作成されていたら valuemapid をインクリメント
            if ($isValuemapCreated) {
                $newValuemapid++;
            }
        }
    }
}