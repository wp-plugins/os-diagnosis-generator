<?php
if(class_exists('DiagnosisView')){
	if(empty($data)){
		$message .= 'データがありません！';
	}
	//
	$class = new DiagnosisView();
	$user_contents = '';

	if(isset($error_jscript)){
		$user_contents .= $error_jscript;
	}

$user_page_view=<<<_EOD_
	<div id="diagnosis-plugin">
		<div class="diagnosis-wrap">
			<div class="red_message">{$message}</div>
_EOD_
;
$user_contents .= $user_page_view."\n";

	if(!empty($data)){

			// フォームタイトル
			if(!empty($data['form_title_flag'])){
				$form_title = '<h3 class="diagnosis-form-title">'.self::h($data['form_title']).'</h3>';
			}else{
				$form_title = '';
			}
			// フォームヘッダ
			$form_header = '';
			if(!empty($data['display_flag'])){
				if(empty($data['after_header_flag'])){
					if(!empty($data['form_header'])){
						$form_header = self::h_dec($data['form_header']);
					}
				}else{
					if(!empty($data['form_after_header'])){
						$form_header = self::h_dec($data['form_after_header']);
					}
				}
			}

$user_page_view=<<<_EOD_
			<div id="diagnosis-form">
				<div class="diagnosis-form-header">
					{$form_header}
				</div>
				{$form_title}
_EOD_
;
$user_contents .= $user_page_view."\n";

$user_contents .= nl2br($result);

			// フォームフッタ
			$form_footer = '';
			if(!empty($data['display_flag'])){
				if(empty($data['after_footer_flag'])){
					if(!empty($data['form_footer'])){
						$form_footer = self::h_dec($data['form_footer']);
					}
				}else{
					if(!empty($data['form_after_footer'])){
						$form_footer = self::h_dec($data['form_after_footer']);
					}
				}
			}

$user_page_view=<<<_EOD_
			</div>
			<div class="diagnosis-form-footer">
				{$form_footer}
			</div>
_EOD_
;
$user_contents .= $user_page_view."\n";

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