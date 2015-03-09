<?php
if(class_exists('DiagnosisAdmin')){
?>
	<div id="diagnosis-plugin">
	<?php include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-head.php"); ?>
		<div class="diagnosis-wrap">
			<h2>はじめに</h2>
			<div class="diagnosis-contents">
				<p>診断ジェネレータ作成プラグインを導入していただき、ありがとうございます。</p>
				<p>当プラグインのご利用は無料です。個人サイトでも商用サイトでも利用できますが、必ず<a href="?page=diagnosis-generator-agreement.php">利用規約</a>をご覧ください。</p>
				<p>ご連絡は<a href="http://lp.olivesystem.jp/plugin-dg-mail" title="問い合わせ" target="_blank">問い合わせフォーム</a>からお願い致します。</p>
			</div>
			<h2>最低動作環境</h2>
			<div class="diagnosis-contents">
				WordPress2.8以上、JQuery1.7以上、ユーザのブラウザでJQueryが動作すること。
			</div>
			<h2>主な特徴</h2>
			<div class="diagnosis-contents">
				<h3 class="green">設置が簡単</h3>
				<p>診断フォームは記事に指定のショートコードを埋め込むだけで表示可能です。複数の診断フォームを表示することもできます。</p>
				<h3 class="green">2つの診断方法</h3>
				<p>診断には2種類の方法があります。名前で自動判断する「名前式」と設問形式で点数から判断する「設問式」です。</p>
				<p>名前式は、ユーザの負担が少なく、名前を入力するだけで診断が自動で完了します。その分、結果はユーザとの親和性がありません。<br />一方、設問式は、いくつかの設問にユーザが答える必要がありますが、その分、親和性があります。</p>
				<h3 class="green">テキストパターン</h3>
				<p>診断結果のテキストパターンは豊富に設定できます。</p>
			</div>
			<h2>更新履歴</h2>
			<div class="diagnosis-contents">
				<p>2015.02.11 軽微な修正。診断結果にリンクを使用できるタグを追加。</p>
				<p>2015.02.07 軽微な修正。利用規約を改定。</p>
				<p>2014.08.21 診断結果にH1～H5タグを使用できるタグ追加。フォントカラーを指定できるタグ追加。フォントサイズを指定できるタグを追加。</p>
				<p>2014.08.18 診断フォーム内のヘッダ、フッタ表示修正</p>
				<p>2014.08.08 リリース</p>
			</div>
		</div>
	</div>

<?php
}
?>