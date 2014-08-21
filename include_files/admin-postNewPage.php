<?php
if(class_exists('DiagnosisAdmin')){

	$display_none1 = 'display:none;';
	// 診断ができるユーザの処理
	if(isset($osdg_option_data) && isset($osdg_option_data['post_authority'])){
		if($osdg_option_data['post_authority']==3){
			$post_authority = 1;
			$display_none1 = '';
		}else{
			$post_authority = $osdg_option_data['post_authority'];
		}
	}else{
		$post_authority = 1;
	}

?>

<script>
// 読み込み時の動作
j(document).ready(function(){
	// 詳細設定の表示 /////////////////////////////////
	var display_flag = j('#display_flag').val();
	if(display_flag==1){
		table_display(display_flag);
		result_page_view(0);
		etc_view();
	}
	// 設問 /////////////////////////////////
	if(j('#diagnosis_type1').is(':checked')){
		change_display_question(1);
		var diagnosis_count = j('#diagnosis_count').val(); // 設問数
		change_display_question_count(diagnosis_count);
		j('.condition').css('display', 'block');
	}
	j("#diagnosis_count").change(function(){
		var str = j(this).val();
		change_display_question_count(str);
	});
	// Text5～10の表示 /////////////////////////////////
	var textgo = j('#textgoInp').val();
	if(textgo==1){
		display_textarea();
	}
});
function table_display(str){
	if(str==0){
		j('.display_option').css('display', 'none');
		j('#display_flag').val(0);
		result_page_view(-1);
		change_display_val('image-textarea', 'result_type_flag', 2)
		j('#image-textarea').css('display', 'none');
	}else{
		j('.display_option').css('display', 'table-row');
		j('#display_flag').val(1);
		etc_view();
	}
}
// 診断結果表示の別URLの表示
function result_page_view(str){
	if(str==1){
		change_display_val('result_url_tr', 'result_page', 1);
		j('#result-shortcode').css('display', 'block');
	}
	else if(str==-1){
		change_display_val('result_url_tr', 'result_page', 0);
		j('#result-shortcode').css('display', 'none');
	}
	else{
		if(j('#result_page_a').is(':checked')){
			j('#result_url_tr').css('display', 'none');
			j('#result-shortcode').css('display', 'none');
		}
		if(j('#result_page_b').is(':checked')){
			j('#result_url_tr').css('display', 'table-row');
			j('#result-shortcode').css('display', 'block');
		}
	}
}
// 諸々表示
function etc_view(){
	//
	if(j('#after_header_flag_b').is(':checked')){
		j('#after_header').css('display', 'block');
	}
	if(j('#after_footer_flag_b').is(':checked')){
		j('#after_footer').css('display', 'block');
	}
	// Image1の表示
	if(j('#rtype_flag_b').is(':checked')){
		j('#image-textarea').css('display', 'block');
	}
}
function change_display_val(ids1, ids2, str){
	if(str==0){
		j('#'+ids1).css('display', 'none');
		j("input[name='"+ids2+"']:eq(0)").attr("checked", true);
	}
	else if(str==1){
		j('#'+ids1).css('display', 'table-row');
		j("input[name='"+ids2+"']:eq(1)").attr("checked", true);
	}
	else if(str==2){
		j('#'+ids1).css('display', 'none');
		j("input[name='"+ids2+"']:eq(0)").attr("checked", true);
	}
	else if(str==3){
		j('#'+ids1).css('display', 'block');
		j("input[name='"+ids2+"']:eq(1)").attr("checked", true);
	}
}
function change_checked(ids, str){
	j("input[name='"+ids+"']:eq("+str+")").attr("checked", true);
}
function change_display_question(str){
	if(str==0){
		change_display_val('question_view', 'diagnosis_type', 0);
		j('#diagnosis_count_tr').css('display', 'none');
		j('.condition').css('display', 'none');
	}
	else{
		change_display_val('question_view', 'diagnosis_type', 3);
		j('#diagnosis_count_tr').css('display', 'table-row');
		j('.condition').css('display', 'block');
	}
}
function change_display_question_count(str){
	if(str==5){
		j('.q-zero').css('display', 'block');
		j('.q-five').css('display', 'none');
		j('.q-ten').css('display', 'none');
		j('.q-twfive').css('display', 'none');
	}
	else if(str==10){
		j('.q-zero').css('display', 'block');
		j('.q-five').css('display', 'block');
		j('.q-ten').css('display', 'none');
		j('.q-twfive').css('display', 'none');
	}
	else if(str==25){
		j('.q-zero').css('display', 'block');
		j('.q-five').css('display', 'block');
		j('.q-ten').css('display', 'block');
		j('.q-twfive').css('display', 'none');
	}
	else if(str==50){
		j('.q-zero').css('display', 'block');
		j('.q-five').css('display', 'block');
		j('.q-ten').css('display', 'block');
		j('.q-twfive').css('display', 'block');
	}
}
function change_display_hf(id, str){
	if(str==0){ // 表示
		change_display_val(id, id+'_flag', 2);
	}
	else{ // 非表示
		change_display_val(id, id+'_flag', 3);
	}
}
function textarea_in(str){
	var now_text = j('#diagnosis-text').text();
	j('#diagnosis-text').text(now_text+'['+str+']');
}
function textarea_inh(str, str_style){
	var now_text = j('#diagnosis-text').text();
	if(str_style){
		j('#diagnosis-text').text(now_text+'['+str+':'+str_style+'][/'+str+']');
	}else{
		j('#diagnosis-text').text(now_text+'['+str+'][/'+str+']');
	}
}
function textarea_default(ids){
	var vtext = '';
	if(ids=='diagnosis-text'){
		vtext = "[Name]は[Text1]で[Text2]です。\n[Text3]で[Text4]になるでしょう。";
	}
	j('#'+ids).text(vtext);
}
function display_textarea(){
	display_block('cutText');
	display_block('textno');
	display_none('textgo');
	j('#textgoInp').val(1);
}
function display_textarea_none(){
	display_block('textgo');
	display_none('cutText');
	display_none('textno');
	j('#textgoInp').val(0);
}
function condition_plus(ids, str){
	var i = parseInt(j('#condition_ct'+str).val());
	var num = i + 1;
	var id_html = j('#'+ids).html();
	var plus = '<p>'+num+'行目：<input type="text" name="before_condition['+str+']['+num+']" placeholder="" value="" class="inp">点以上<input type="text" name="after_condition['+str+']['+num+']" placeholder="" value="" class="inp">点未満</p>';
	j('#'+ids).html(id_html+plus);
	j('#condition_ct'+str).val(num);
}
function checkbox_onoff(ids){
	if(j('#'+ids).is(':checked')){
		j('#'+ids).attr("checked", false);
	}
	else{
		j('#'+ids).attr("checked", true);
	}
}
function view_message(ids, str){
	var vtext = '';
	// 診断の基本設定
	if(str=='title'){
		vtext = '診断フォームのタイトルを入力します。必須項目です。';
	}
	if(str=='label'){
		vtext = '診断フォームの入力欄のラベルを入力します。ラベルを空にすればラベルは表示されません。';
	}
	if(str=='placeholder'){
		vtext = '診断フォームの入力欄に表示するプレースホルダを入力します。';
	}
	if(str=='submit'){
		vtext = '診断フォームの送信ボタンに表示するテキストを入力します。空の場合はデフォルトの「診断する」を適用します。';
	}
	if(str=='authority'){
		vtext = '診断を実行できるユーザは、登録ユーザのみかゲストを含んだ全てのユーザか設定します。';
	}
	if(str=='diagnosis_type'){
		vtext = '診断方法を選択します。[診断はシステムに任せる]を選択すると、ユーザからの名前入力をもとに当システムが自動診断します。 [診断を設問形式にする]を選択すると、設問、選択肢、点数、点数による診断結果の設定が必要です。';
	}
	if(str=='diagnosis_count'){
		vtext = '診断に使用する設問数を選択します。最大は50問です。';
	}
	if(str=='rpage'){
		vtext = '診断結果を診断フォームの埋め込み先に表示するなら「同一ページに表示する」を選択、埋め込み先とは別のページに診断結果を埋め込むなら「別ページに表示する」を選択する。別ページにした場合は、該当記事に必ず結果表示用のショートコードを埋め込んでください。';
	}
	if(str=='rurl'){
		vtext = '「別ページに表示する」を選択した場合、こちらは必ず設定してください。診断後にリダイレクトされます。';
	}
	if(str=='class'){
		vtext = '診断フォームをdivタグで囲みますが、そのdivタグのclassの指定です。複数指定する場合は半角スペースで区切ってください。';
	}
	if(str=='rtype'){
		vtext = '診断結果に画像を含ませる場合は「画像を含ませる」を選択してください。画像は診断結果のテキストの上に表示されます。';
	}
	if(str=='fheader'){
		vtext = 'フォームのヘッダー部分に表示します。診断フォームより上に表示されます。フォームの説明文などによいでしょう。';
	}
	if(str=='ffooter'){
		vtext = 'フォームのフッター部分に表示します。診断フォームより下に表示されます。診断作成者のサイトやSNSリンクによいでしょう。';
	}
	//　診断結果を設定
	if(ids=='push_message0'){
		vtext = 'ユーザに表示する診断結果テキストの全文を入力します。可変するテキストは[Text1]というタグで制御します。';
	}
	if(ids=='push_message1' || ids=='push_message2' || ids=='push_message3' || ids=='push_message4' || ids=='push_message5' || ids=='push_message6' || ids=='push_message7' || ids=='push_message8' || ids=='push_message9' || ids=='push_message10'){
		vtext = '一行ずつ、診断結果のテキストを入力します。最大3,000文字まで記述できます。';
	}
	if(ids=='push_messageImage'){
		vtext = 'ユーザに表示する画像のURLもしくは画像パスを入力します。';
	}
	if(ids=='push_questionView'){
		vtext = '設問文は、診断に必要なユーザへの質問文章を入力します。<br />選択肢は、その設問文に対する選択肢を入力します。一行ずつ入力してください。選択肢が3つなら3行になります。<br />点数は、選択肢ごとの点数を入力します。選択肢1に5点なら一行目に「5」を入力します。';
	}
	open_message(ids, vtext);
}
</script>

<?php if(isset($error_jscript)){ echo $error_jscript; } ?>

	<div id="diagnosis-plugin">
		<div class="diagnosis-wrap">
		<?php if(isset($write_id)): ?>
			<h2>診断フォームを編集</h2>
		<?php else: ?>
			<h2>診断フォームを新規作成</h2>
		<?php endif; ?>
			<div class="diagnosis-contents">

				<?php if(isset($write_id)): ?>

				<h3>ショートコード</h3>
				<div class="short-tag">
					<textarea readonly>[OSDGSIS-FORM id=<?php echo $write_id; ?>]</textarea>
					<br />
				</div>
				<div id="result-shortcode" class="short-tag" style="display:none;">
					<span>結果表示用</span><textarea readonly>[OSDGSIS-RESULT-FORM id=<?php echo $write_id; ?>]</textarea>
				</div>
				<form action="admin.php?page=diagnosis-generator-write.php&write_id=<?php echo $write_id; ?>" method="POST">

				<?php else: ?>

				<h3>ショートコード</h3>
				<div class="short-tag">
					<textarea readonly>[OSDGSIS-FORM id=?]</textarea>
					<br />?にはidが入ります
				</div>
				<div id="result-shortcode" class="short-tag" style="display:none;">
					<span>結果表示用</span><textarea readonly>[OSDGSIS-RESULT-FORM id=?]</textarea>
				</div>
				<form action="admin.php?page=diagnosis-generator-new.php" method="POST">

				<?php endif; ?>

					<div class="red_message"><?php echo $message; ?></div>
					<h3>診断の基本設定</h3>
					<div class="post_options">
						<span onclick="table_display(0)" class="pointer">標準設定</span><span onclick="table_display(1)" class="pointer-l">詳細設定を有効</span>
					</div>
					<div class="cont">
						<div id="view_message"></div>
						<table class="line">
							<tr>
								<th class="l"><label for="form_title">タイトル</label></th>
								<td class="c">
									<input type="text" name="form_title" id="form_title" value="<?php self::post_set('form_title'); ?>" placeholder="診断フォーム" class="input" /><br />
									<?php
										$checked_page0 = '';
										$checked_page1 = ' checked';
										$checked = self::post_set('form_title_flag', 1);
										if(empty($checked)){ $checked_page0 = ' checked'; $checked_page1 =''; }
									?>
									<input type="radio" name="form_title_flag" id="form_title_flag1" value="1"<?php echo $checked_page1; ?> /><span onclick="change_checked('form_title_flag', 0)">フォームタイトルを表示する</span>
									<input type="radio" name="form_title_flag" id="form_title_flag0" value="0"<?php echo $checked_page0; ?> /><span onclick="change_checked('form_title_flag', 1)">表示しない</span>
									<span onclick="view_message('view_message', 'title')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr>
								<th class="l"><label for="form_input_label">ラベル</label></th>
								<td class="c">
									<input type="text" name="form_input_label" id="form_input_label" value="<?php self::post_set('form_input_label'); ?>" placeholder="名前で診断" class="input" />
									<span onclick="view_message('view_message', 'label')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr>
								<th class="l"><label for="form_input_placeholder">プレースホルダ</label></th>
								<td class="c">
									<input type="text" name="form_input_placeholder" id="form_input_placeholder" value="<?php self::post_set('form_input_placeholder'); ?>" placeholder="鈴木一郎" class="input" />
									<span onclick="view_message('view_message', 'placeholder')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr>
								<th class="l"><label for="form_submit_text">送信ボタンのテキスト</label></th>
								<td class="c">
									<input type="text" name="form_submit_text" id="form_submit_text" value="<?php self::post_set('form_submit_text'); ?>" placeholder="診断する" class="input" />
									<span onclick="view_message('view_message', 'submit')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr style="<?php echo $display_none1; ?>">
								<th class="l">診断ができるユーザ</th>
								<td class="c">
									<?php
										$checked = self::post_set('post_authority', 1);
										if(!empty($checked)){ $post_authority = $checked; }
									?>
									<select name="post_authority" class="input">
										<option value="1" <?php if($post_authority==1){ ?>selected<?php } ?>>全ユーザが利用できる</option>
										<option value="2" <?php if($post_authority==2){ ?>selected<?php } ?>>登録ユーザが利用できる</option>
									</select>
									<span onclick="view_message('view_message', 'authority')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr>
								<th class="l"><label>診断方法</label></th>
								<td class="c">
									<?php
										$checked_page0 = ' checked';
										$checked_page1 = '';
										$checked = self::post_set('diagnosis_type', 1);
										if(!empty($checked)){ $checked_page1 = ' checked'; $checked_page0 =''; }
									?>
									<input type="radio" name="diagnosis_type" id="diagnosis_type0" onclick="change_display_question(0)" value="0"<?php echo $checked_page0; ?> /><span onclick="change_display_question(0)">診断はシステムに任せる</span>
									<input type="radio" name="diagnosis_type" id="diagnosis_type1" onclick="change_display_question(1)" value="1"<?php echo $checked_page1; ?> /><span onclick="change_display_question(1)">診断を設問形式にする</span>
									<span onclick="view_message('view_message', 'diagnosis_type')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr id="diagnosis_count_tr" style="display:none;">
								<th class="l"><label>設問数</label></th>
								<td class="c">
									<?php
										$checked = self::post_set('diagnosis_count', 1);
										if(!empty($checked)){ $diagnosis_count = $checked; }else{ $diagnosis_count = 10; }
									?>
									<select name="diagnosis_count" id="diagnosis_count" class="input" on>
										<option value="5" <?php if($diagnosis_count==5){ ?>selected<?php } ?> onclick="change_display_question_count(5)">5問</option>
										<option value="10" <?php if($diagnosis_count==10){ ?>selected<?php } ?>>10問</option>
										<option value="25" <?php if($diagnosis_count==25){ ?>selected<?php } ?>>25問</option>
										<option value="50" <?php if($diagnosis_count==50){ ?>selected<?php } ?>>50問</option>
									</select>
									<span onclick="view_message('view_message', 'diagnosis_count')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr class="display_option" style="display:none;">
								<th class="l">診断結果の表示</th>
								<td class="c">
									<?php
										$checked_page0 = ' checked';
										$checked_page1 = '';
										$checked = self::post_set('result_page', 1);
										if(!empty($checked)){ $checked_page1 = ' checked'; $checked_page0 =''; }
									?>
									<input type="radio" name="result_page" id="result_page_a" onclick="result_page_view(-1)" value="0"<?php echo $checked_page0; ?> /><span onclick="result_page_view(-1)">同一ページに表示する</span>　
									<input type="radio" name="result_page" id="result_page_b" onclick="result_page_view(1)" value="1"<?php echo $checked_page1; ?> /><span onclick="result_page_view(1)">別ページに表示する</span>
									<span onclick="view_message('view_message', 'rpage')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr id="result_url_tr" style="display:none;">
								<th class="l"><label for="result_page_url">診断結果表示URL</label></th>
								<td class="c">
									<input type="text" name="result_page_url" id="result_page_url" value="<?php self::post_set('result_page_url'); ?>" placeholder="http://www.example.com/?p=1" class="input" />
									<span onclick="view_message('view_message', 'rurl')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr class="display_option" style="display:none;">
								<th class="l">診断フォームclass</th>
								<td class="c">
									<input type="text" name="form_class" id="form_class" value="<?php self::post_set('form_class'); ?>" placeholder="form-class" class="input" />
									<span onclick="view_message('view_message', 'class')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr class="display_option" style="display:none;">
								<th class="l">画像の利用</th>
								<td class="c">
									<?php
										$checked_page0 = ' checked';
										$checked_page1 = '';
										$checked = self::post_set('result_type_flag', 1);
										if(!empty($checked)){ $checked_page1 = ' checked'; $checked_page0 =''; }
									?>
									<input type="radio" name="result_type_flag" id="rtype_flag_a" onclick="change_display_val('image-textarea', 'result_type_flag', 2)" value="0"<?php echo $checked_page0; ?> /><span onclick="change_display_val('image-textarea', 'result_type_flag', 2)">診断結果に画像を含まない</span>　
									<input type="radio" name="result_type_flag" id="rtype_flag_b" onclick="change_display_val('image-textarea', 'result_type_flag', 3)" value="1"<?php echo $checked_page1; ?> /><span onclick="change_display_val('image-textarea', 'result_type_flag', 3)">画像を含ませる</span>
									<span onclick="view_message('view_message', 'rtype')" class="pointer setsu">説明</span>
								</td>
							</tr>
							<tr class="display_option" style="display:none;">
								<th class="l">ヘッダー</th>
								<td class="c">
									<?php
										$checked_page0 = ' checked';
										$checked_page1 = '';
										$checked = self::post_set('after_header_flag', 1);
										if(!empty($checked)){ $checked_page1 = ' checked'; $checked_page0 =''; }
									?>
									<textarea name="form_header" id="form_header" placeholder="診断フォームのヘッダーに表示したい文章を入力します" class="hfder"><?php self::post_set('form_header', 3); ?></textarea>
									<br />
									<input type="radio" name="after_header_flag" value="0" onclick="change_display_hf('after_header', 0)"<?php echo $checked_page0; ?> /><span onclick="change_display_hf('after_header', 0)">診断結果も同一ヘッダーを使用する</span>　
									<input type="radio" name="after_header_flag" id="after_header_flag_b" value="1" onclick="change_display_hf('after_header', 1)"<?php echo $checked_page1; ?> /><span onclick="change_display_hf('after_header', 1)">使用しない</span>
									<div id="after_header" style="display:none;">
										<textarea name="form_after_header" id="form_after_header" placeholder="診断結果のヘッダーに表示したい文章を入力します" class="hfder"><?php self::post_set('form_after_header', 3); ?></textarea>
									</div>
									<span onclick="view_message('view_message', 'fheader')" class="pointer setsu-m">説明</span>
								</td>
							</tr>
							<tr class="display_option" style="display:none;">
								<th class="l">フッター</th>
								<td class="c">
									<?php
										$checked_page0 = ' checked';
										$checked_page1 = '';
										$checked = self::post_set('after_footer_flag', 1);
										if(!empty($checked)){ $checked_page1 = ' checked'; $checked_page0 =''; }
									?>
									<textarea name="form_footer" id="form_footer" placeholder="診断フォームのフッターに表示したい文章を入力します" class="hfder"><?php self::post_set('form_footer', 3); ?></textarea>
									<br />
									<input type="radio" name="after_footer_flag" value="0" onclick="change_display_hf('after_footer', 0)"<?php echo $checked_page0; ?> /><span onclick="change_display_hf('after_footer', 0)">診断結果も同一フッターを使用する</span>　
									<input type="radio" name="after_footer_flag" id="after_footer_flag_b" value="1" onclick="change_display_hf('after_footer', 1)"<?php echo $checked_page1; ?> /><span onclick="change_display_hf('after_footer', 1)">使用しない</span>
									<div id="after_footer" style="display:none;">
										<textarea name="form_after_footer" id="form_after_footer" placeholder="診断結果のフッターに表示したい文章を入力します" class="hfder"><?php self::post_set('form_after_footer', 3); ?></textarea>
									</div>
									<span onclick="view_message('view_message', 'ffooter')" class="pointer setsu-m">説明</span>
								</td>
							</tr>
						</table>
					</div>
					<br />
					<div class="cont question-input" id="question_view" style="display:none;">
						<h3>設問文と点数を設定</h3>
						<?php
						$div_class = 'q-zero';
						$div_style = '';
						//
						for($i=0; $i<50; $i++){
							$t = $i + 1;
							switch($i){
								case 5:
									$div_class = 'q-five';
									break;
								case 10:
									$div_class = 'q-ten';
									$div_style = 'display:none;';
									break;
								case 25:
									$div_class = 'q-twfive';
									break;
							}
							//
							$question_text_set = 'question.'.$t.'.text';
							$question_choice_set = 'question.'.$t.'.choice';
							$question_point_set = 'question.'.$t.'.point';
						?>

						<div class="<?php echo $div_class; ?>" style="<?php echo $div_style; ?>">
							<div class="dh">問<?php echo $t; ?><?php if($i==0){ ?><small onclick="view_message('push_questionView')">説明</small><?php } ?></div>
							<div id="push_questionView"></div>
							<div class="clearfix question-box">
								<div class="left textarea-text">
									<div class="dh dhq">設問文
										<small onclick="text_clear('question-text<?php echo $t; ?>')">クリア</small>
									</div>
									<textarea name="question[<?php echo $t; ?>][text]" id="question-text<?php echo $t; ?>" <?php if($i==0){ ?>placeholder="設問文章をここに入力します。例：あなたは何型ですか？"<?php } ?>><?php self::post_set($question_text_set); ?></textarea>
								</div>
								<div class="left textarea-choice">
									<div class="dh dhq">選択肢
										<small onclick="text_clear('question-choice<?php echo $t; ?>')">クリア</small>
									</div>
									<textarea name="question[<?php echo $t; ?>][choice]" id="question-choice<?php echo $t; ?>" <?php if($i==0){ ?>placeholder="回答の選択肢を一行ずつ入力します。例：A型です"<?php } ?>><?php self::post_set($question_choice_set); ?></textarea>
								</div>
								<div class="left textarea-point">
									<div class="dh dhq">点数
										<small onclick="text_clear('question-point<?php echo $t; ?>')">クリア</small>
									</div>
									<textarea name="question[<?php echo $t; ?>][point]" id="question-point<?php echo $t; ?>" <?php if($i==0){ ?>placeholder="5"<?php } ?>><?php self::post_set($question_point_set); ?></textarea>
								</div>
							</div>
						</div>
						<?php } ?>
						<br />
					</div>
					<h3>診断結果を設定</h3>
					<div class="cont diagnosis-input">
						<div class="dh">診断結果のテキスト
							<small onclick="view_message('push_message0')">説明</small>
							<small onclick="text_clear('diagnosis-text')">クリア</small>
							<small onclick="textarea_default('diagnosis-text')">デフォルトに戻す</small>
						</div>
						<div>
							使用できるタグ [Name]、[Text1]～[Text10]、[H1]～[H5]、[COLOR:色]、[SIZE:サイズ]<br />
							H1～H5タグ、COLORタグ、SIZEタグは必ず閉じタグを入力してください（例：[H1]テキスト[/H1]）。
						</div>
						<div id="push_message0"></div>
						<div class="clearfix">
							<?php
								$checked = self::post_set('result_text', 1);
								if(!empty($checked)){ $result_text = $checked; }
								else{ $result_text = "[Name]は[Text1]で[Text2]です。\n[Text3]で[Text4]になるでしょう。"; }
							?>
							<textarea name="result_text" id="diagnosis-text" class="left"><?php echo $result_text; ?></textarea>
							<div id="tags-box" class="box left">
								<p>クリックで追加</p>
								<span onclick="textarea_in('Name')">Name</span><span onclick="textarea_in('Text1')">Text1</span><span onclick="textarea_in('Text2')">Text2</span><span onclick="textarea_in('Text3')">Text3</span><span onclick="textarea_in('Text4')">Text4</span><span onclick="textarea_in('Text5')">Text5</span><span onclick="textarea_in('Text6')">Text6</span><span onclick="textarea_in('Text7')">Text7</span><span onclick="textarea_in('Text8')">Text8</span><span onclick="textarea_in('Text9')">Text9</span><span onclick="textarea_in('Text10')">Text10</span><span onclick="textarea_inh('H1')">H1</span><span onclick="textarea_inh('H2')">H2</span><span onclick="textarea_inh('H3')">H3</span><span onclick="textarea_inh('COLOR', 'red')">COLOR</span><span onclick="textarea_inh('SIZE', '20')">SIZE</span>
							</div>
						</div>
						<br />
						<div id="image-textarea" style="display:none;">
							<div class="dh">診断結果の画像
								<small onclick="view_message('push_messageImage')">説明</small>
								<small onclick="text_clear('image1')">クリア</small>
							</div>
							<div id="push_messageImage"></div>
							<div class="clearfix">
								<textarea name="image1" id="image1" placeholder="診断結果の画像URLもしくはパス" class="left"><?php self::post_set('image1'); ?></textarea>
								<div class="condition condition-disp left" style="display:none;">
									<div class="chd">表示条件</div>
									<div id="condition-line1001">

									<?php
									for($i=0; $i<1000; $i++){
										$t = $i + 1;
										$after_placeholder = $t * 10;
										$before_placeholder = $after_placeholder - 10;
										//
										// post_set用
										$before_condition_set = 'before_condition.1001.'.$t;
										$after_condition_set = 'after_condition.1001.'.$t;
										$after_data = self::post_set($after_condition_set, 1);
										//
										if(3<$i && empty($after_data)){
											break;
										}
									?>
										<p><?php echo $t; ?>行目：<input type="text" name="before_condition[1001][<?php echo $t; ?>]" placeholder="<?php echo $before_placeholder; ?>" value="<?php self::post_set($before_condition_set); ?>" class="inp">点以上<input type="text" name="after_condition[1001][<?php echo $t; ?>]" placeholder="<?php echo $after_placeholder; ?>" value="<?php self::post_set($after_condition_set); ?>" class="inp">点未満</p>

									<?php
									}
									?>
									</div>
									<input type="button" name="plus_condition" value="条件を追加" onclick="condition_plus('condition-line1001', '2')" class="plus-condition" />
									<input type="hidden" name="condition_ct1001" id="condition_ct1001" value="<?php echo $t; ?>" />
								</div>
							</div>
							<br />
						</div>

						<?php
						for($i=1; $i<11; $i++){
							$text_set = 'text'.$i;
						?>

							<?php
							if($i==5){
								$check_textarea = self::post_set($text_set, 1);
								if(!empty($check_textarea)){
									$textgo_flag = 1;
								}
							?>

						<p id="textgo">Text5～Text10は省略されています。<span onclick="display_textarea()" class="pointer-l">表示する</span></p>
						<p id="textno" style="display:none;">Text5～Text10を<span onclick="display_textarea_none()" class="pointer">表示しない</span></p>
						<br />
						<div id="cutText" style="display:none;"><!--// cutText Start -->

							<?php
							}
							?>
						<div class="dh">Text<?php echo $i; ?>
							<small onclick="view_message('push_message<?php echo $i; ?>')">説明</small>
							<small onclick="text_clear('text<?php echo $i; ?>')">クリア</small>
							<?php
							// 編集画面の際、2以降で使用
							if(1<$i && isset($write_id)){
							?>
							<small><input type="checkbox" name="text_delete<?php echo $i; ?>" id="text_delete<?php echo $i; ?>" value="1" /><span onclick="checkbox_onoff('text_delete<?php echo $i; ?>')">削除する</span></small>
							<?php
							}
							?>
						</div>
						<div id="push_message<?php echo $i; ?>"></div>
						<div class="clearfix">
							<textarea name="text<?php echo $i; ?>" id="text<?php echo $i; ?>" placeholder="診断結果Text<?php echo $i; ?>" class="left"><?php self::post_set($text_set); ?></textarea>
							<div class="condition condition-disp left" style="display:none;">
								<div class="chd">表示条件</div>
								<div id="condition-line<?php echo $i; ?>">

							<?php
							$hidden_name = 'condition_ct'.$i;
							//
							if(!empty($post) && !empty($post[$hidden_name])){
								$ln_end = intval($post[$hidden_name]) + 1;
							}else{
								$ln_end = 5;
							}
							for($ln=1; $ln<$ln_end; $ln++){
								$after_placeholder = $ln * 10;
								$before_placeholder = $after_placeholder - 10;
								// name用
								$before_condition_name = 'before_condition['.$i.']['.$ln.']';
								$after_condition_name = 'after_condition['.$i.']['.$ln.']';
								// post_set用
								$before_condition_set = 'before_condition.'.$i.'.'.$ln;
								$after_condition_set = 'after_condition.'.$i.'.'.$ln;
							?>
								<p><?php echo $ln; ?>行目：<input type="text" name="<?php echo $before_condition_name; ?>" placeholder="<?php echo $before_placeholder; ?>" value="<?php self::post_set($before_condition_set); ?>" class="inp">点以上<input type="text" name="<?php echo $after_condition_name; ?>" placeholder="<?php echo $after_placeholder; ?>" value="<?php self::post_set($after_condition_set); ?>" class="inp">点未満</p>
							<?php } ?>

								</div>
								<input type="button" name="plus_condition" value="条件を追加" onclick="condition_plus('condition-line<?php echo $i; ?>', '<?php echo $i; ?>')" class="plus-condition" />
								<input type="hidden" name="<?php echo $hidden_name; ?>" id="<?php echo $hidden_name; ?>" value="4" />
							</div>
						</div>
						<br />

						<?php
						}
						if(isset($textgo_flag)){
							$checked = 1;
						}else{
							$checked = self::post_set('textgo', 1);
						}
						if(empty($checked)){ $textgo = 0; }else{ $textgo = $checked; }
						?>

						<input type="hidden" name="textgo" id="textgoInp" value="<?php echo $textgo; ?>">
						</div><!--// cutText End -->
					</div>
					<?php
						$checked = self::post_set('display_flag', 1);
						if(empty($checked)){ $display_flag = 0; }else{ $display_flag = $checked; }
					?>
					<input type="hidden" name="display_flag" id="display_flag" value="<?php echo $display_flag; ?>" />

				<?php if(isset($write_id)): ?>

					<input type="hidden" name="write" value="1" />
					<input type="hidden" name="data_id" value="<?php echo $write_id; ?>" />
					<div class="submit"><input type="submit" name="submit" value="更新する" /></div>

				<?php else: ?>

					<input type="hidden" name="new" value="1" />
					<div class="submit"><input type="submit" name="submit" value="作成する" /></div>

				<?php endif; ?>

				</form>
			</div>
		</div>
	</div>

<?php
}
?>