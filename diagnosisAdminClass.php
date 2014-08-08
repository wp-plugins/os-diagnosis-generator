<?php
class DiagnosisAdmin extends DiagnosisClass {

	public function __construct(){

		parent::__construct();
		// まず実行
		add_action('admin_init', array('DiagnosisAdmin', 'actionAdminInit'));
		// 管理画面メニュー
		add_action('admin_menu', array('DiagnosisAdmin', 'menuViews'));

	}
	/*
	*  プラグインメニュー
	*/
	// メニュー表示
	public function menuViews(){

		global $osdg_option_data; // オプションデータ
		global $osdg_user_data; // ユーザデータ
		self::postAction($osdg_user_data);

		// メニュー表示
		add_menu_page('診断ジェネレータ作成プラグイン', '診断ジェネレータ作成プラグイン', 'administrator', 'diagnosis-generator-admin.php', array('DiagnosisAdmin', 'adminPage'));
		add_submenu_page('diagnosis-generator-admin.php', '新規作成', '新規作成', 'administrator', 'diagnosis-generator-new.php', array('DiagnosisAdmin', 'postNewPage'));
		add_submenu_page('diagnosis-generator-admin.php', '診断フォーム一覧', '診断フォーム一覧', 'administrator', 'diagnosis-generator-list.php', array('DiagnosisAdmin', 'postListPage'));
		add_submenu_page('diagnosis-generator-admin.php', 'オプション', 'オプション', 'administrator', 'diagnosis-generator-options.php', array('DiagnosisAdmin', 'optionPage'));
		// メニューに非表示するページ
		add_submenu_page('diagnosis-generator-options.php', '利用規約', null, 'administrator', 'diagnosis-generator-agreement.php', array('DiagnosisAdmin', 'agreementPage'));
		add_submenu_page('diagnosis-generator-new.php', '編集', null, 'administrator', 'diagnosis-generator-write.php', array('DiagnosisAdmin', 'postWritePage'));

	}
	//
	private function postAction(){

		global $osdg_user_data; // ユーザデータ

		// diagnosis-generatorの場合に実行
		if(isset($_GET['page']) && (stristr($_GET['page'], "diagnosis-generator") || stristr($_POST['page'], "diagnosis-generator"))){

			// ゲストは管理画面を表示させない。トップページへ
			if($osdg_user_data['level']=='guest'){

				wp_safe_redirect(home_url('/'));
				exit;

			// 管理者のときの処理
			}elseif($osdg_user_data['level']=='administrator'){

				// POST処理
				if(isset($_POST['format'])){
					self::formatPlugin();
				}elseif(isset($_POST['option'])){
					self::optionPost();
				}

			}

		}

	}
	/*
	*  ページビュー
	*/
	// Page はじめに
	public function adminPage(){

		self::makeTableCheck();
		include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-adminPage.php");

	}
	// Page 利用規約
	public function agreementPage(){

		self::makeTableCheck();
		include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-agreementPage.php");

	}
	// Page 基本設定
	public function optionPage(){

		self::makeTableCheck();
		global $osdg_option_data;
		$message = DiagnosisMessageClass::updateMessage();
		include OSDG_PLUGIN_DIR."class/timezoneList.php";
		include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-optionPage.php");

	}
	// Page　新規作成
	public function postNewPage(){

		self::makeTableCheck();
		global $osdg_option_data;
		global $osdg_user_data;
		$message = '';

		if(!empty($_POST['submit'])){
			$post = self::post_escape();
			$post_question = $post['question'];
			unset($post['question']);
			$validate = self::validation_postNew($post); // バリデーション
			// 設問形式なら、そちらもバリデーション
			if($post['diagnosis_type']==1){
				$question_validate = self::validation_postNew_question($post_question, $post['diagnosis_count']); // 設問バリデーション
				if(!empty($question_validate)){
					$validate = array_merge($validate, $question_validate);
				}
			}
			// 通過
			if($result = DiagnosisValidationClass::validates($validate)){ // チェックOK
				$return_id = DiagnosisSqlClass::new_diagnosis($post);
				if($post['diagnosis_type']==1){
					DiagnosisSqlClass::new_diagnosis_question($post_question, $return_id);
				}
				// リダイレクト
				if(!empty($return_id)){
					self::os_redirect(admin_url('/').'admin.php?page=diagnosis-generator-new.php&id='.$return_id.'&msg=insert-ok');
				}else{
					self::os_redirect(admin_url('/').'admin.php?page=diagnosis-generator-new.php&msg=insert-ng');
				}
				exit;
			}else{ // チェックNG
				$error_jscript = DiagnosisValidationClass::js_error_css($validate);
				$message .= DiagnosisValidationClass::pmessage($validate);
			}
		}

		$message .= DiagnosisMessageClass::updateMessage();
		include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-postNewPage.php");

	}
	// Page 編集
	public function postWritePage(){

		self::makeTableCheck();
		global $osdg_option_data;
		global $osdg_user_data;
		$message = '';
		$write_id = trim(self::h($_GET['write_id']));
		// POST時
		if(!empty($_POST['submit'])){
			$post = self::post_escape();
			$post_question = $post['question'];
			unset($post['question']);
			$validate = self::validation_postNew($post); // バリデーション
			// 設問形式なら、そちらもバリデーション
			if($post['diagnosis_type']==1){
				$question_validate = self::validation_postNew_question($post_question, $post['diagnosis_count']); // 設問バリデーション
				if(!empty($question_validate)){
					$validate = array_merge($validate, $question_validate);
				}
			}
			// 通過
			if($result = DiagnosisValidationClass::validates($validate)){ // チェックOK
				$return_id = DiagnosisSqlClass::write_diagnosis($post);
				if($post['diagnosis_type']==1){
					DiagnosisSqlClass::write_diagnosis_question($post_question, $return_id);
				}
				// リダイレクト
				if(!empty($return_id)){
					self::os_redirect(admin_url('/').'admin.php?page=diagnosis-generator-write.php&write_id='.$return_id.'&msg=update-ok');
				}else{
					self::os_redirect(admin_url('/').'admin.php?page=diagnosis-generator-write.php&write_id='.$write_id.'&msg=update-ng');
				}
				exit;
			}else{ // チェックNG
				$error_jscript = DiagnosisValidationClass::js_error_css($validate);
				$message .= DiagnosisValidationClass::pmessage($validate);
			}
		// 通常
		}else{
			$data = DiagnosisSqlClass::get_diagnosis($write_id);
			self::action_cache_write($data, 'data');
		}

		$message .= DiagnosisMessageClass::updateMessage();
		include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-postWritePage.php");
		self::action_cache_delete();

	}
	// Page 診断フォーム一覧
	public function postListPage(){

		self::makeTableCheck();
		$message = '';
		$data = DiagnosisSqlClass::get_list_diagnosis();
		$message .= DiagnosisMessageClass::updateMessage();
		include_once(OSDG_PLUGIN_INCLUDE_FILES."/admin-postListPage.php");


	}
	/*
	*  初期設定
	*/
	// 初期設定
	public function firstOption(){

		// 設定を初期化
		update_option(OSDG_PLUGIN_VERSION_NAME, OSDG_PLUGIN_VERSION);
		update_option(OSDG_PLUGIN_TABLE_VERSION_NAME, OSDG_PLUGIN_TABLE_VERSION);
		$arr = array(
			'post_authority'=>'1', 'table_ok'=>0,
		);
		update_option(OSDG_PLUGIN_DATA_NAME, $arr);

	}
	// ちゃんとテーブルが作成されているかチェック
	public function makeTableCheck(){

		global $osdg_option_data;

		if(empty($osdg_option_data['table_ok'])){
			if(!DiagnosisSqlClass::show_table(OSDG_PLUGIN_TABLE_NAME)){
				$error = 1;
			}
			if(!DiagnosisSqlClass::show_table(OSDG_PLUGIN_DETAIL_TABLE_NAME)){
				$error = 1;
			}
			if(!DiagnosisSqlClass::show_table(OSDG_PLUGIN_QUESTION_TABLE_NAME)){
				$error = 1;
			}
		}
		//
		if(isset($error)){
			print '<div style="margin:10px 0 10px 0;color:red;">テーブルが正しく作成されておりません！いますぐ<a href="?page=diagnosis-generator-options.php">オプション</a>で初期化を実行してください（初期化するとテーブルが作成されます）。</div>';
		}else{
			$arr = $osdg_option_data;
			$arr['table_ok'] = 1;
			update_option(OSDG_PLUGIN_DATA_NAME, $arr);
		}

	}
	/*
	*  設定ページ
	*/
	// プラグインが初期化されたときに実行する
	private function formatPlugin(){

		delete_option(OSDG_PLUGIN_DATA_NAME);
		global $wpdb;
		// テーブルが存在するか確認
		$table_exists = DiagnosisSqlClass::show_table(OSDG_PLUGIN_TABLE_NAME);
		// テーブルが存在すればデータ削除、なければテーブルを新規作成
		if($table_exists){
			DiagnosisSqlClass::deleteTable();
		}else{
			DiagnosisSqlClass::newTable();
		}

		self::firstOption();

		// リダイレクト
		if(get_option(OSDG_PLUGIN_VERSION_NAME)){
			wp_safe_redirect(admin_url('/').'admin.php?page=diagnosis-generator-options.php&msg=format-ok');
			exit;
		}else{
			wp_safe_redirect(admin_url('/').'admin.php?page=diagnosis-generator-options.php&msg=format-error');
			exit;
		}

	}
	/*
	*  メニューを呼び出す前に実行
	*/
	public function actionAdminInit(){

		// jQuery
		wp_enqueue_script('jquery');
		// Javascript
		$dir_ex = explode("/", rtrim(OSDG_PLUGIN_DIR, "/")); // 現在のプラグインのパス
		$now_plugin = end($dir_ex); // 現在のプラグインのディレクトリ名
		wp_enqueue_script('j', plugins_url($now_plugin).'/js/j.js', array(), '1.0');
		// css
		wp_enqueue_style('style-admin', plugins_url($now_plugin).'/style-admin.css', array(), '1.0');

	}
	// 基本設定、POSTの処理
	private function optionPost(){

		$update_array = parent::arrayData($_POST);
		update_option(OSDG_PLUGIN_DATA_NAME, $update_array);

		// リダイレクト
		if(get_option(OSDG_PLUGIN_DATA_NAME)){
			wp_safe_redirect(admin_url('/').'admin.php?page=diagnosis-generator-options.php&msg=update-ok');
			exit;
		}else{
			wp_safe_redirect(admin_url('/').'admin.php?page=diagnosis-generator-options.php&msg=update-ng');
			exit;
		}

	}
	/*
	*  バリデーションチェック
	*/
	// 新規作成バリデーション
	private function validation_postNew($post=''){

		$validate = array();

		foreach($post as $key => $p){
			if(stristr($key, 'before_condition') || stristr($key, 'after_condition')){ // 何もしない

			}else{ // バリデーション

				switch($key){
					// 何もしない
					case 'display_flag': case 'result_page': case 'result_type_flag': case 'diagnosis_type': case 'textgo': case 'after_header_flag': case 'after_footer_flag': case 'form_title_flag':
						break;
					// バリデーションは文字数だけ（150文字）
					case 'result_page_url': case 'form_submit_text': case 'form_input_label': case 'form_input_placeholder': case 'form_class':
						$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array(array('number', 0, 150)));
						break;
					// バリデーションは文字数だけ（2万文字）
					case 'form_header': case 'form_footer': case 'form_after_header': case 'form_after_footer':
						$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array(array('number', 0, 20000)));
						break;
					// Text系、3000文字
					case 'text1': case 'text2': case 'text3': case 'text4': case 'text5': case 'text6': case 'text7': case 'text8': case 'text9': case 'text10': case 'image1':
						$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array(array('number', 0, 3000)));
						break;
					// デフォルト、空はエラー、151文字以上はエラー
					default:
						$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array('empty', array('number', 0, 150)));
				}
				// 結合
				if(!empty($validate)){
					$validate = array_merge($validate, $this_validate);
				}else{
					$validate = $this_validate;
				}

			}

		}
		// メッセージを修正
		$change_arr = array(
			'form_title'=>'タイトル', 'form_class'=>'診断フォームclass', 'form_input_label'=>'ラベル', 'form_submit_text'=>'送信ボタンのテキスト', 'form_input_placeholder'=>'プレースホルダ', 'result_page_url'=>'診断結果表示URL', 'result_page'=>'診断結果の表示', 'form_header'=>'ヘッダー', 'form_footer'=>'フッター',
		);
		$validate = DiagnosisValidationClass::validates_message($validate, $change_arr);

		return $validate;

	}
	// 設問用バリデーション
	private function validation_postNew_question($post='', $count='10'){

		$validate = array();
		$validate_question = array();
		//
		for($i=0; $i<$count; $i++){
			$t = $i + 1;
			$post_question = $post[$t];
			$toi = '問'.$t.'の';
			//
			foreach($post_question as $key => $p){
				switch($key){
					case 'point':
						// 空はエラー、201文字以上はエラー
						$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array('empty', array('number', 0, 200)));
						break;
					default:
						// 空はエラー、501文字以上はエラー
						$this_validate = DiagnosisValidationClass::validation_rule($p, $key, array('empty', array('number', 0, 500)));
				}
				// 結合
				$validate_question = array_merge($validate_question, $this_validate);
				// メッセージを修正
				$change_arr = array(
					'text'=>$toi.'設問', 'choice'=>$toi.'選択肢', 'point'=>$toi.'点数',
				);
				$validate_question = DiagnosisValidationClass::validates_message($validate_question, $change_arr);
			}
			// エラーがあれば
			if(!empty($validate_question)){
				$validate_combine = array();
				// キーを修正
				foreach($validate_question as $key => $val){
					switch($key){
						case 'text':
							$k = 'question-text'.$t;
							break;
						case 'choice':
							$k = 'question-choice'.$t;
							break;
						case 'point':
							$k = 'question-point'.$t;
							break;
						default:
							$k = $key;
					}
					//
					$validate_combine[$k] = $val;
				}
				// 結合
				if(!empty($validate)){
					$validate = array_merge($validate, $validate_combine);
				}else{
					$validate = $validate_combine;
				}
				unset($validate_question); unset($validate_combine);
			}
			$validate_question = array();
		}

		return $validate;

	}

}
?>