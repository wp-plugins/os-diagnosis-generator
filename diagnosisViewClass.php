<?php
class DiagnosisView extends DiagnosisClass {

	public function __construct(){

		parent::__construct();
		// 表示のショートコード
		add_shortcode('OSDGSIS-FORM', array('DiagnosisView', 'viewMode'));
		add_shortcode('OSDGSIS-RESULT-FORM', array('DiagnosisView', 'viewResultMode')); // 診断結果のショートコード
		// JS、CSS読み込み
		add_action('wp_print_scripts', array('DiagnosisView', 'os_wp_enqueue'));

	}
	/*
	*  ショートコードの処理
	*/
	public function viewMode($atts, $content=null){

		extract(shortcode_atts(array(
			'id' => '',
		), $atts));
		// 処理用に配列にする
		$arr = array(
			'id'=>$id,
		);
		//
		if(empty($_GET['osdgl'])){
			$content = do_shortcode(self::shortcode_view($content, $arr));
		}else{
			if(!empty($_GET['osdgid'])){ // idが指定されていれば
				$arr['id'] = self::h($_GET['osdgid']);
			}
			$content = do_shortcode(self::shortcode_result_view($content, $arr));
		}

		return $content;

	}
	// 診断結果(個別の場合に使用)
	public function viewResultMode($atts, $content=null){

		$GLOBALS['osdgsis_plugin'] = 1;

		extract(shortcode_atts(array(
			'id' => '',
		), $atts));
		// 処理用に配列にする
		$arr = array(
			'id'=>$id,
		);
		if(!empty($_GET['osdgid'])){ // idが指定されていれば
			$arr['id'] = self::h($_GET['osdgid']);
		}
		$content = do_shortcode(self::shortcode_result_view($content, $arr));

		return $content;

	}
	/*
	*  診断の表示
	*/
	// 診断フォームの表示
	public function shortcode_view($content='', $arr=array()){

		$message = '';

		if(!empty($arr['id'])){
			// データ
			$data_arr = DiagnosisSqlClass::get_diagnosis_plurality($arr['id']);
			// POST時
			if(!empty($_POST['diagnosis_plugin'])){
				$post = self::post_escape();
				// データid
				if(!empty($post['diagnosis_id'])){
					$data_id = $post['diagnosis_id'];
				}elseif(!empty($arr['id'])){
					$data_id = $arr['id'];
				}
				// 投稿権限チェック
				$post_authority = self::post_authority_check($data_id, $data_arr);
				// 権限OKなら
				if($post_authority==0){
					$validate = self::validation_post($post); // バリデーション
					// 通過
					if($result = DiagnosisValidationClass::validates($validate)){ // チェックOK
						$check_data = DiagnosisSqlClass::get_diagnosis($data_id); // データ取得
						$return_data = self::diagnosis_post($check_data, $post); // データを元に解析
						$url_name = urlencode($post['diagnosis_name']);
						// 成功なら
						if(!empty($return_data['line']) || !empty($return_data['img'])){
							// 結果表示が別ページに設定されていれば
							if(!empty($check_data['display_flag']) && !empty($check_data['result_page']) && !empty($check_data['result_page_url'])){
								$jump_url = trim($check_data['result_page_url']);
							}else{
								$jump_url = '';
							}
							$url = self::url_plus(array('osdgl'=>$return_data['line'], 'osdgimg'=>$return_data['img'], 'osdgid'=>$data_id, 'osdgn'=>$url_name), $jump_url);
						}else{
							$url = self::url_plus(array('msg'=>'dg-error'));
						}
						// リダイレクト
						self::os_redirect($url);
					}else{ // チェックNG
						$error_jscript = DiagnosisValidationClass::js_error_css($validate);
						$message .= DiagnosisValidationClass::pmessage($validate);
					}
				}else{ // 投稿権限NG
					$message .= "このフォームは登録ユーザのみ使用できます。";
				}
			}

			$message .= DiagnosisMessageClass::updateMessage('1');
			include_once(OSDG_PLUGIN_INCLUDE_FILES."/user-viewFormPage.php");

		}

		return $content;

	}
	// 投稿権限チェック
	private function post_authority_check($data_id='', $data=''){

		global $osdg_option_data; // オプションデータ
		global $osdg_user_data; // ユーザデータ
		$user_level = $osdg_user_data['level'];
		$refusal = 1; // 1で拒否
		// 設定があれば実行
		if(isset($osdg_option_data['post_authority'])){
			switch($osdg_option_data['post_authority']){
				case '2': // 登録ユーザのみが実行できる
					if($user_level!='guest'){ // ゲストじゃなければ
						$refusal = 0;
					}
					break;
				case '3': // フォームごとに設定
					foreach($data as $d){
						if($d['data_id']==$data_id){
							switch($d['post_authority']){
								case '2':
									if($user_level!='guest'){ // ゲストじゃなければ
										$refusal = 0;
									}
									break;
								default:
									$refusal = 0;
							}
							break;
						}
					}
					break;
				default:
					$refusal = 0;
			}
		}else{ // なければフォーム実行可能にする
			$refusal = 0;
		}

		return $refusal;

	}
	// 診断結果の表示
	public function shortcode_result_view($content='', $arr=array()){

		$get = self::get_escape();
		$message = '';

		if((!empty($_GET['osdgid']) || !empty($arr['id'])) && isset($get['osdgl']) && isset($get['osdgn'])){
			//
			if(!empty($_GET['osdgid'])){
				$data_id = self::h($_GET['osdgid']);
			}elseif(!empty($arr['id'])){
				$data_id = $arr['id'];
			}
			// データ
			$data = DiagnosisSqlClass::get_diagnosis($data_id);
			$result = DiagnosisResultClass::result_data_arrangement($get, $data);
			$message .= DiagnosisMessageClass::updateMessage('1');
			include_once(OSDG_PLUGIN_INCLUDE_FILES."/user-viewResultPage.php");

		}

		return $content;

	}
	/*
	*  POST時の処理
	*/
	private function diagnosis_post($data='', $post=''){

		$return_data = array('line', 'img');
		$name = self::h($_POST['diagnosis_name']);
		// システムに任せた診断
		if(isset($data['diagnosis_type']) && $data['diagnosis_type']==0){
			// 処理
			$hash_array = DiagnosisResultClass::hash_len($name);
			$result_text = DiagnosisResultClass::result_text_arr($data); // Text系
			$result_text_img = DiagnosisResultClass::result_text_arr($data, 'image'); // Image系
			$return_data = DiagnosisResultClass::result_system($result_text, $result_text_img, $hash_array);
		// 設問形式の診断
		}elseif(isset($data['diagnosis_type']) && $data['diagnosis_type']==1){
			// 処理
			$return_data = DiagnosisResultClass::result_qsystem($post, $data);
		}

		return $return_data;

	}
	/*
	*  Javascript CSS 呼び出し
	*/
	public function os_wp_enqueue(){

		if(self::has_shortcode('OSDGSIS-FORM')==TRUE || self::has_shortcode('OSDGSIS-RESULT-FORM')==TRUE){
			// jQuery
			wp_enqueue_script('jquery');
			// Javascript
			$dir_ex = explode("/", rtrim(OSDG_PLUGIN_DIR, "/")); // 現在のプラグインのパス
			$now_plugin = end($dir_ex); // 現在のプラグインのディレクトリ名
			wp_enqueue_script('j', plugins_url($now_plugin).'/js/j.js', array(), '1.0');
			// css
			wp_enqueue_style('style', plugins_url($now_plugin).'/style.css', array(), '1.0');
		}

	}
	// ショートコードがあるか否か
	private function has_shortcode($shortcode){

		global $wp_query;
		// 記事データを取得
		if(isset($wp_query->post)){
			$post = $wp_query->post;
			if(isset($post->post_content)){
				$post_data = $post->post_content;
			}
		}
		//　取得できなければ別の配列からもう一度試みる
		if(isset($wp_query->posts) && !isset($post_data)){
			$posts = $wp_query->posts;
			if(isset($posts[0]) && isset($posts[0]->post_content)){
				$post_data = $posts[0]->post_content;
			}
		}
		// 投稿データにショートコードが含まれるか
		if(isset($post_data)){
			// 含めばTRUE
			if(stristr($post_data, "[".$shortcode)){
				return TRUE;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}

	}
	/*
	*  ビューで使用する関数
	*/
	public function if_empty_checked($str=''){

		if(empty($checked)){
			return 'checked';
		}else{
			return '';
		}

	}
	public function if_ecall_checked($str1='', $str2=''){

		if($str1==$str2){
			return 'checked';
		}else{
			return '';
		}

	}
	/*
	*  バリデーションチェック
	*/
	// 診断バリデーション
	private function validation_post($post=''){

		$validate = array();

		foreach($post as $key => $p){
			switch($key){
				case 'diagnosis_name':
					// 空はNG、文字数は50文字まで
					$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array('empty', array('number', 0, 50)));
					break;
				case 'question':
					$this_validate = self::question_validation_post($p);
					break;
			}
			// 結合
			if(!empty($validate)){
				$validate = array_merge($validate, $this_validate);
			}else{
				$validate = $this_validate;
			}
		}

		// メッセージを修正
		$change_arr = array(
			'diagnosis_name'=>'名前',
		);
		$validate = DiagnosisValidationClass::validates_message($validate, $change_arr);

		return $validate;

	}
	// 上記で使用
	private function question_validation_post($post=''){

		foreach($post as $key => $p){
			// 空はNG
			$this_validate = DiagnosisValidationClass::validation_rule($p, 'question'.$key, array('select-empty'));
			// 結合
			if(!empty($validate)){
				$validate = array_merge($validate, $this_validate);
			}else{
				$validate = $this_validate;
			}
		}
		// メッセージを修正
		$change_arr = array(
			'question'=>'問',
		);
		$validate = DiagnosisValidationClass::validates_message($validate, $change_arr);

		return $validate;

	}

}
?>
