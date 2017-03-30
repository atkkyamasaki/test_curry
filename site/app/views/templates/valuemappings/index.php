<p><strong>テスト中なのでまだ商用で利用しないで下さい！</strong></p>
<p>TODO:接続先DB変更機能</p>
<p><strong>現在は インポート/エクスポートともに 商用DB に接続しにいきます。</strong></p>
<p>インポート元の環境でインポートすると JSON が得られます。</p>
<p>JSON をコピーした上で、エクスポート先の環境でインポートを選択してください。</p>
<h3>STEP1. 値のマッピングの JSON を入手</h3>
<p>JSON の入手には 2 つの方法があります。
<h4>1. DB からエクスポート or インポート する場合</h4>
<p>エクスポートしたい値のマッピングがあるサーバのエンドポイントでログインして以下のリンクをクリックしてください。</p>
<p><a href="<?php echo $helper->createUrl(['valuemappings', 'export']); ?>">ログイン中のサーバから値のマッピング JSON を入手</a></p>
<h4>2. CSV ファイルから JSON を入手</h4>
<p>エクスポートしたい値のマッピングを CSV ファイルにして以下のフォームに送信してください。</p>
<h4>CSVのサンプル</h4>
<p>左：管理画面の値のマッピング 右：CSVファイルサンプル</p>
<p><strong>※注意※ ExcelでCSVを作成すると「true」「false」が自動的に大文字になってしまいます。</strong></p>
<p><strong>※注意※ CSVを作成する時は「元の文字列のまま」作成してください。</strong></p>
<img src="/images/valuemap_gui.png" width="540" height="600" alt="GUI上のバリューマップ">
<img src="/images/valuemap_csv.png" width="540" height="600" alt="CSV化した状態（このファイルをアップロードしてください）">
<form action="<?php echo $helper->createUrl(['valuemappings', 'csv']); ?>" method="post" enctype="multipart/form-data">
  <label for="csv">JSON にしたい値のマッピング CSV File</label>
  <input id="csv" type="file" name="csv">
  <input type="submit">
</form>
<h3>STEP2. JSON からDBへ値のマッピングを追加</h3>
<p>JSON を入手したら以下のリンク先で貼り付けて実行してください</p>
<p><a href="<?php echo $helper->createUrl(['valuemappings', 'import']); ?>">STEP2 インポートサーバでログインして JSON を貼り付け</a></p>
