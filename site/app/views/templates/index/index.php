<p>Zabbix API TEST</p>
<h3>TODO</h3>
<ul>
  <li>[Zabbix API] CSV出力機能</li>
  <li>[Zabbix API] 件数表示（GET、成功、失敗、追加、更新、など）</li>
  <li>[Zabbix API] 更新メソッド追加</li>
  <li>[Zabbix API] 既存の置換え、存在チェック</li>
  <li>[Zabbix API] テンプレートを名前からidに変換する機能</li>
  <li>[Zabbix API] <s>リクエスト＆レスポンスログ書き出し機能</s> → 済み</li>
</ul>
<h3>更新履歴</h3>
<ul>
  <li>2016年7月25日 [MIB] <a href="/mibs">MIBファイルを操作する用ページ作成</a></li>
  <li>2016年7月13日 [Zabbix API] Zabbix API 操作ログ機能を追加 /var/www/console/zabbix-api-wrapper/site/logs/zabbix_api.log</li>
  <li>2016年7月13日 [Zabbix API] 入力されたデータの前後のホワイトスペースを削除する仕組みを追加</li>
  <li>2016年6月30日 [回線障害・工事情報] <a href="/line">回線障害・工事情報スクレイピング機能追加</a></li>
  <li>2016年5月11日 [Syslog] <a href="/syslog">Syslog2.0検証<a/></li>
  <li>2016年5月11日 [Zabbix API] <a href="/">Zabbix API操作基盤</a></li>
  <li>2016年5月11日 開発開始</li>
</ul>
<h3>個別メニュー</h3>
<ul>
  <li><s><a href="<?php echo $helper->createUrl('sesc'); ?>">SESC API 検証用</a></s></li>
  <li><a href="<?php echo $helper->createUrl('ping'); ?>">ホスト一覧csvの生成</a></li>
  <li><s><a href="<?php echo $helper->createUrl('line'); ?>">回線工事・障害情報</a></s></li>
</ul>
