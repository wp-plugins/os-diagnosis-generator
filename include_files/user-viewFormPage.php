<?php
if(class_exists('DiagnosisView')){
	if(empty($data_arr)){
		$message .= 'データがありません！';
	}
	//
	$class = new DiagnosisView();
	$user_contents = '';
	// POSTの場合
	if(!empty($post) && !empty($post['diagnosis_id'])){
		$check_pid = $post['diagnosis_id'];
	}

$user_page_view=<<<_EOD_
<script>
function click_views(ids){
	j('#'+ids).css("display", "block");
}
function click_views_none(ids){
	j('#'+ids).css("display", "none");
}
</script>
_EOD_
;
$user_contents .= $user_page_view."\n";

	if(isset($error_jscript)){
		$user_contents .= $error_jscript;
	}

$user_page_view=<<<_EOD_
	<div id="diagnosis-plugin">
		<div class="diagnosis-wrap">
_EOD_
;
$user_contents .= $user_page_view."\n";
	// POSTじゃなければヘッダ側にエラーテキスト
	if(!isset($check_pid)){
		$user_contents .= "<div class=\"red_message\">{$message}</div>\n";
	}
	// データがあれば
	if(!empty($data_arr)){

		foreach($data_arr as $data){
			// submitボタンの値
			if(!empty($data['form_submit_text'])){
				$sbm_text = self::h($data['form_submit_text']);
			}else{
				$sbm_text = '診断する';
			}
			// プレースホルダ
			if(!empty($data['form_input_placeholder'])){
				$placeholder = self::h($data['form_input_placeholder']);
			}else{
				$placeholder = '';
			}
			// フォームタイトル
			if(!empty($data['form_title_flag'])){
				$form_title = '<h3 class="diagnosis-form-title">'.self::h($data['form_title']).'</h3>';
			}else{
				$form_title = '';
			}
			// フォームヘッダ
			if(!empty($data['display_flag']) && !empty($data['form_header'])){
				$form_header = self::h_dec($data['form_header']);
			}else{
				$form_header = '';
			}
			// フォームclass
			if(!empty($data['display_flag']) && !empty($data['form_class'])){
				$css_class = self::h($data['form_class']);
			}else{
				$css_class = '';
			}
			//
			if(isset($check_pid) && $check_pid==$data['data_id']){ // 該当idなら
				$error_message = "<div class=\"red_message\">{$message}</div>\n";
			}else{
				$error_message = '';
			}

$user_page_view=<<<_EOD_
			<div id="osdg-form{$data['data_id']}" class="diagnosis-form">
				<div class="diagnosis-form-header">
					{$form_header}
				</div>
				{$form_title}
				{$error_message}
				<form action="#osdg-form{$data['data_id']}" method="POST" class="{$css_class}">
_EOD_
;
$user_contents .= $user_page_view."\n";

			if(!empty($data['form_input_label'])){

$user_page_view=<<<_EOD_
					<span class="label">
						<label for="diagnosis-name">{$class->h($data['form_input_label'])}</label>
					</span>
_EOD_
;
$user_contents .= $user_page_view."\n";

			}

$user_page_view=<<<_EOD_
					<span class="cols">
						<input type="text" name="diagnosis_name" id="diagnosis_name" placeholder="{$placeholder}" value="{$class->post_set('diagnosis_name', '1')}" />
					</span>
_EOD_
;
$user_contents .= $user_page_view."\n";

				if(isset($data['diagnosis_type']) && $data['diagnosis_type']==0){
				// システム任せのフォーム //////////////////////////////////

$user_page_view=<<<_EOD_
					<span class="submit">
						<input type="submit" name="submit" value="{$sbm_text}" />
					</span>
_EOD_
;
$user_contents .= $user_page_view."\n";

				}elseif(isset($data['diagnosis_type']) && $data['diagnosis_type']==1){
				// 設問形式のフォーム //////////////////////////////////
					if(!empty($data['question'])){
						$q = 1;

						// 設問表示 start
						foreach($data['question'] as $key => $question){
							$sid = $question['sort_id'];
							$line = explode("\n", trim($question['choice']));
							$l = 1;
							if(empty($question['text'])){
								break;
							}
							$checked = self::post_set('question.'.$sid, 1);

$user_page_view=<<<_EOD_
					<div class="question">
						<div class="qcontents">問{$q} : {$question['text']}</div>
						<div class="qselect" id="question{$q}">
							<input type="radio" name="question[{$sid}]" value="0" style="display:none;" {$class->if_empty_checked($checked)} />
_EOD_
;
$user_contents .= $user_page_view."\n";

							foreach($line as $ln){

$user_page_view=<<<_EOD_
							<span><input type="radio" name="question[{$sid}]" value="{$l}" {$class->if_ecall_checked($checked, $l)} />{$ln}</span>
_EOD_
;
$user_contents .= $user_page_view."\n";

								$l++;
							}
							$q++;

$user_page_view=<<<_EOD_
						</div>
					</div>
_EOD_
;
$user_contents .= $user_page_view."\n";

						}
						// 設問表示 end
						// 設問data_id start
						if(isset($data['question'][0]) && isset($data['question'][0]['data_id'])){

$user_page_view=<<<_EOD_
					<input type="hidden" name="data_id" value="{$data['question'][0]['data_id']}" />
_EOD_
;
$user_contents .= $user_page_view."\n";

						} // 設問data_id end

$user_page_view=<<<_EOD_
					<br />
					<div class="submit">
						<input type="submit" name="submit" value="{$sbm_text}" />
					</div>
_EOD_
;
$user_contents .= $user_page_view."\n";

					}else{

$user_page_view=<<<_EOD_
					<div class="red_message">設問がありません</div>
_EOD_
;
$user_contents .= $user_page_view."\n";

					}

				}

			// フォームフッタ
			if(!empty($data['display_flag']) && !empty($data['form_footer'])){
				$form_footer = self::h_dec($data['form_footer']);
			}else{
				$form_footer = '';
			}

$user_page_view=<<<_EOD_
					<input type="hidden" name="diagnosis_id" value="{$data['data_id']}" />
					<input type="hidden" name="diagnosis_plugin" value="1" />
				</form>
				<div class="diagnosis-form-footer">
					{$form_footer}
				</div>
			</div>
_EOD_
;
$user_contents .= $user_page_view."\n";

		}
	}
	$user_contents .= self::osdgLicense(1);

$user_page_view=<<<_EOD_
		</div>
	</div>
_EOD_
;
$user_contents .= $user_page_view."\n";
$content = $user_contents;
}
?>