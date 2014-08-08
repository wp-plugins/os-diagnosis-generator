<?php
// SQLを操作するcalss
class DiagnosisSqlClass extends DiagnosisClass {

	public function __construct(){

		parent::__construct();

	}
	/*
	*  管理画面でのSQL
	*/
	/*
	*  一覧取得
	*/
	public function get_list_diagnosis($paged='1', $ct='20'){

		$data = array();
		//
		if(empty($paged)){
			$paged = 1;
		}
		// 検索条件
		if($paged=='all'){
			$limit = "";
		}else{
			$after_cases = $paged * $ct;
			$before_cases = $after_cases - $ct;
			$limit = " LIMIT ".$before_cases.",".$ct;
		}
		//
		global $wpdb;
		$sql = "SELECT * FROM `".OSDG_PLUGIN_TABLE_NAME."` WHERE `delete_flag`= %s ORDER by `data_id` ASC".$limit;
		$params = array('0');
		$get_data = $wpdb->get_results( $wpdb->prepare($sql, $params) );
		// データがあれば
		if(!empty($get_data) && isset($get_data[0]->data_id)){
			// 処理
			foreach($get_data as $key => $get){
				// 整理
				foreach($get as $k => $g){
					$data[$key][$k] = $g;
				}
				// Text系データを取得
				$id = $get->data_id;
				$detail_sql = "SELECT * FROM `".OSDG_PLUGIN_DETAIL_TABLE_NAME."` WHERE `delete_flag`='0' AND `data_id`= %d";
				$detail_params = array($id);
				$detail_data = $wpdb->get_results( $wpdb->prepare($detail_sql, $detail_params) );
				// Text系データがあれば
				if(!empty($detail_data) && isset($detail_data[0]->detail_data_id)){
					$data[$key] = self::detail_arrangement_data($detail_data, $data[$key]);
				}
				// 診断タイプが設問形式なら、設問データも取得する
				if(isset($data[0]->diagnosis_type) && $data[0]->diagnosis_type==1){
					$data[$key]['question'] = self::get_question($id);
				}else{
					$data[$key]['question'] = '';
				}
			}
		}

		return $data;

	}
	/*
	*  指定idデータの取得
	*/
	// データ取得
	public function get_diagnosis($id='', $type=''){

		$return_data = array();
		$data = array();
		$detail_data = array();
		$question_data = array();

		if(!empty($id)){
			// SQL
			global $wpdb;
			$sql = "SELECT * FROM `".OSDG_PLUGIN_TABLE_NAME."` WHERE `delete_flag`='0' AND `data_id`= %d";
			$params = array($id);
			$get_data = $wpdb->get_results( $wpdb->prepare($sql, $params) );
			// データがあれば
			if(!empty($get_data) && isset($get_data[0]->data_id)){
				//
				foreach($get_data as $get){
					foreach($get as $k => $g){
						$data[$k] = $g;
					}
				}
				// Text系データ
				$id = $get_data[0]->data_id;
				$detail_sql = "SELECT * FROM `".OSDG_PLUGIN_DETAIL_TABLE_NAME."` WHERE `delete_flag`='0' AND `data_id`= %d";
				$detail_params = array($id);
				$detail_data = $wpdb->get_results( $wpdb->prepare($detail_sql, $detail_params) );
				// Text系データがあれば
				if(!empty($detail_data) && isset($detail_data[0]->detail_data_id)){
					$data = self::detail_arrangement_data($detail_data, $data);
				}
				// 診断タイプが設問形式なら、設問データも取得する
				if(isset($get_data[0]->diagnosis_type) && $get_data[0]->diagnosis_type==1){
					$question_data = self::get_question($id);
				}else{
					$question_data = '';
				}

				$return_data = $data;
				$return_data['question'] = $question_data;

			}

		}

		return $return_data;

	}
	// 複数のデータ取得
	public function get_diagnosis_plurality($ids='', $type=''){

		$return_data = array();
		$id_ex = explode(",", $ids);
		//
		foreach($id_ex as $id){
			$data = self::get_diagnosis($id, $type);
			if(!empty($data)){
				$return_data[] = $data;
			}
		}

		return $return_data;

	}
	// 設問データの取得
	public function get_question($id=''){

		$question_data = array();
		global $wpdb;
		$sql = "SELECT * FROM `".OSDG_PLUGIN_QUESTION_TABLE_NAME."` WHERE `delete_flag`='0' AND `data_id`= %d ORDER by `sort_id` ASC";
		$params = array($id);
		$get_data = $wpdb->get_results( $wpdb->prepare($sql, $params) );
		// データがあれば
		if(!empty($get_data) && isset($get_data[0]->question_id)){
			foreach($get_data as $gdata){
				foreach($gdata as $k => $g){
					if(stristr($k, "question_")){
						$key = str_replace("question_", "", $k);
						$data[$key] = $g;
					}else{
						$data[$k] = $g;
					}
				}
				$sort_id = $gdata->sort_id;
				$question_data[$sort_id] = $data;
			}
		}

		return $question_data;

	}
	// 詳細データの処理
	public function detail_arrangement_data($detail_data='', $data=''){

		if(!empty($detail_data)){

			$data['before_condition'] = array();
			$data['after_condition'] = array();

			foreach($detail_data as $dd){
				$tid = $dd->pattern_id;
				if(999<$tid){
					$m_tid = $tid - 1000;
					$col = 'image'.$m_tid;
				}else{
					$col = 'text'.$tid;
				}
				$data[$col] = $dd->result_text;
				$conditions = $dd->condition;
				// 設問条件のデータ処理、存在すれば
				if(!empty($conditions)){
					$ln = 1;
					$conditions_line = explode("\n", $conditions);
					foreach($conditions_line as $cond){
						if(!empty($cond)){
							$cond_ex = explode(",", $cond);
							$data['before_condition'][$tid][$ln] = $cond_ex[0];
							$data['after_condition'][$tid][$ln] = $cond_ex[1];
							$ln++;
						}
					}
				}
			}

		}

		return $data;

	}
	/*
	*  診断の新規作成
	*/
	// 診断設定、テキスト作成
	public function new_diagnosis($post=''){

		$return_id = 0;
		ksort($post); // キーによる昇順
		global $wpdb;
		//
		$into = '( ';
		$value = '( ';
		$params = array();
		//
		$detail_into = '';
		$detail_value = '';
		$detail_params = array();
		$text_i = 1;
		// 設問形式のとき
		$condition_line = self::condition_line($post);
		// 処理
		foreach($post as $key => $p){
			if($key=='form_title' || $key=='form_input_label' || $key=='form_input_placeholder' || $key=='form_submit_text' || $key=='form_class' || $key=='result_text' || $key=='result_page' || $key=='result_page_url' || $key=='result_type_flag' || $key=='display_flag' || $key=='form_header' || $key=='form_footer' || $key=='form_after_header' || $key=='form_after_footer'){
				$into .= "`".self::sql_escape($key)."`,";
				$value .= "%s,";
				// 条件によって値を変化
				$p = self::post_premise_change($post, $p, $key);
				$params[] = trim($p);
			}
			elseif($key=='post_authority' || $key=='result_page' || $key=='diagnosis_type' || $key=='diagnosis_count' || $key=='after_header_flag' || $key=='after_footer_flag' || $key=='form_title_flag'){
				$into .= "`".self::sql_escape($key)."`,";
				$value .= "%d,";
				$params[] = trim($p);
			}
			elseif($key=='text1' || $key=='text2' || $key=='text3' || $key=='text4' || $key=='text5' || $key=='text6' || $key=='text7' || $key=='text8' || $key=='text9' || $key=='text10' || $key=='image1'){
				if($text_i==1){ // 初回のみ
					$detail_into = "( `data_id`, `pattern_id`, `result_type`, `condition`, `result_text` )";
				}
				$post_data = trim($p);
				//
				if(stristr($key, "text")){
					$result_type = 0;
				}else{
					$result_type = 1;
				}
				// 条件によって値を0にする
				if($key=='image1' && $post['result_type_flag']==0){
					$post_data = '';
				}
				// 空じゃなければ挿入する
				if(!empty($post_data)){
					$d_key = self::data_key($text_i, $key);
					// 設問条件
					if(!empty($condition_line) && isset($condition_line[$d_key])){
						$detail_params[] = $condition_line[$d_key];
					}else{
						$detail_params[] = '';
					}
					$detail_value .= "( '[data_id]', '".$d_key."', '".$result_type."', %s, %s ),"; // [data_id]は後で数値idに変更
					$detail_params[] = $post_data;
					//
					if(!stristr($key, 'image')){
						$text_i++;
					}
				}
			}
		}
		// 作成日時のデータを付加
		$into .= "`create_time` )";
		$value .= "%s )";
		$params[] = date("Y-m-d H:i:s", time());
		// os_diagnosis_generator_dataテーブルにインサート
		$sql = "INSERT INTO `".OSDG_PLUGIN_TABLE_NAME."` ".$into." VALUES ".$value;
		$wpdb->query( $wpdb->prepare($sql, $params) );
		// インサートに成功したら、Text系をインサート
		if($id = $wpdb->insert_id){
			$detail_value = str_replace("[data_id]", $id, rtrim($detail_value, ","));
			$detail_sql = "INSERT INTO `".OSDG_PLUGIN_DETAIL_TABLE_NAME."` ".$detail_into." VALUES ".$detail_value;
			$wpdb->query( $wpdb->prepare($detail_sql, $detail_params) );
			$return_id = $id;
		}

		return $return_id;

	}
	// 設問作成
	public function new_diagnosis_question($post='', $id='0'){

		global $wpdb;
		$into = "( `data_id`, ";
		$value = '';
		$params = array();
		// 処理
		foreach($post as $t => $pos){
			$value .= "( %d, ";
			$params[] = $id;
			$p_value = '';
			foreach($pos as $key => $p){
				if($t==1){ // 初回のみ
					$into .= "`question_".self::sql_escape($key)."`, ";
				}
				$p_value .= "%s, ";
				$params[] = $p;
			}
			$value .= $p_value."%d ),";
			$params[] = $t;
		}

		$into .= "`sort_id` )";
		$sql = "INSERT INTO `".OSDG_PLUGIN_QUESTION_TABLE_NAME."` ".$into." VALUES ".rtrim($value, ",");
		$wpdb->query( $wpdb->prepare($sql, $params) );

	}
	/*
	*  診断の更新
	*/
	public function write_diagnosis($post=''){

		ksort($post);
		$set_data = '';
		$set_detail_data = array();
		$params = array();
		$set_detail_params = array();
		$text_i = 1;
		// 設問形式のとき
		$condition_line = self::condition_line($post);
		// Text系の存在チェック
		$text_check = self::diagnosis_check_wr($post, $condition_line);
		// SQL
		global $wpdb;
		// 処理
		foreach($post as $key => $p){
			if($key=='form_title' || $key=='form_input_label' || $key=='form_input_placeholder' || $key=='form_submit_text' || $key=='form_class' || $key=='result_text' || $key=='result_page' || $key=='result_page_url' || $key=='result_type_flag' || $key=='display_flag' || $key=='form_header' || $key=='form_footer' || $key=='form_after_header' || $key=='form_after_footer'){
				// SET
				$set_data .= "`".self::sql_escape($key)."`= %s , ";
				// 条件によって値を変化
				$p = self::post_premise_change($post, $p, $key);
				$params[] = trim($p);
			}
			elseif($key=='post_authority' || $key=='result_page' || $key=='after_header_flag' || $key=='after_footer_flag' || $key=='form_title_flag'){
				$set_data .= "`".self::sql_escape($key)."`= %d ,";
				$params[] = trim($p);
			}
			elseif($key=='text1' || $key=='text2' || $key=='text3' || $key=='text4' || $key=='text5' || $key=='text6' || $key=='text7' || $key=='text8' || $key=='text9' || $key=='text10' || $key=='image1'){
				$post_data = trim($p);
				// 存在チェック
				if($text_check[$key]==1){
					//
					if(stristr($key, "text")){
						$result_type = 0;
					}else{
						$result_type = 1;
					}
					// 条件によって値を0にする
					if($key=='image1' && $post['result_type_flag']==0){
						$post_data = '';
					}
					// 削除フラグがあれば値を0にする
					$delete_key = str_replace(array('text', 'image'), array('text_delete', 'image_delete'), $key);
					if(!empty($post[$delete_key])){
						$post_data = '';
					}
					// 空じゃなければ挿入する
					if(!empty($post_data)){
						$d_key = self::data_key($text_i, $key);
						// 設問条件
						if(!empty($condition_line) && isset($condition_line[$d_key])){
							$set_detail_params[$d_key][] = $condition_line[$d_key];
						}else{
							$set_detail_params[$d_key][] = '';
						}
						//
						$set_detail_params[$d_key][] = $post_data;
						$set_detail_data[$d_key] = "`condition`= %s , `result_text`= %s ";
						//
						if(!stristr($key, 'image')){
							$text_i++;
						}
					}
				}
			}
		}

		global $wpdb;
		// アップデート
		$sql = "UPDATE `".OSDG_PLUGIN_TABLE_NAME."` SET ".rtrim($set_data, ", ")." WHERE `data_id`= %d AND `delete_flag`='0'";
		$params[] = $post['data_id'];
		$result = $wpdb->query( $wpdb->prepare($sql, $params) );
		//
		if($result===FALSE){
			$return_id = '';
		}else{
			// 詳細のアップデート
			foreach($set_detail_data as $key => $set){
				$dParams = array();
				$dSql = "UPDATE `".OSDG_PLUGIN_DETAIL_TABLE_NAME."` SET ".rtrim($set)." WHERE `data_id`= %d AND `delete_flag`='0' AND `pattern_id`= %d";
				$dParams = $set_detail_params[$key];
				$dParams[] = $post['data_id'];
				$dParams[] = $key;
				$wpdb->query( $wpdb->prepare($dSql, $dParams) );
				unset($dParams);
			}
			$return_id = $post['data_id'];
		}

		return $return_id;

	}
	// Text系が存在するかチェックし、なければ新規挿入にする
	public function diagnosis_check_wr($post, $condition_line){

		$return_array = array(
			'text1'=>0, 'text2'=>0, 'text3'=>0, 'text4'=>0, 'text5'=>0, 'text6'=>0, 'text7'=>0, 'text8'=>0, 'text9'=>0, 'text10'=>0, 'image1'=>0,
		);
		// SQL
		global $wpdb;
		$sql = "SELECT * FROM `".OSDG_PLUGIN_DETAIL_TABLE_NAME."` WHERE `delete_flag`='0' AND `data_id`= %d ORDER by `pattern_id` ASC";
		$params = array($post['data_id']);
		$get_data = $wpdb->get_results( $wpdb->prepare($sql, $params) );
		// データがあれば
		if(!empty($get_data)){
			foreach($get_data as $data){
				$id = $data->pattern_id;
				$detail_id = $data->detail_data_id;
				if(999<$id){
					$m_id = $id - 1000;
					$cols = 'image'.$m_id;
					$delete_cols = 'image_delete'.$id;
				}else{
					$cols = 'text'.$id;
					$delete_cols = 'text_delete'.$id;
				}
				// 存在すればTRUE
				if(isset($return_array[$cols])){
					$return_array[$cols] = 1;
					// 削除が設定されていれば削除してFALSEにする
					if(!empty($post[$delete_cols])){
						self::delete_data(OSDG_PLUGIN_DETAIL_TABLE_NAME, 'detail_data_id', $detail_id);
						$return_array[$cols] = 0;
						unset($post[$cols]);
					}
				}
			}
		}
		// 存在しないものはINSERTする
		$insert_into = "( `data_id`, `pattern_id`, `result_type`, `result_text`, `condition` )";
		$insert_value = '';
		$params = array();
		$i = 0;
		//
		foreach($return_array as $key => $r){
			// 存在せず、中身がPOSTされていれば
			if($r==0 && !empty($post[$key])){
				$conditions = '';
				//
				if(stristr($key, "image")){
					$t = intval(str_replace("image", "", $key));
					$result_type = 1;
				}else{
					$t = intval(str_replace("text", "", $key));
					$result_type = 0;
				}
				$d = self::data_key($t, $key);
				$insert_value .= "( %d, %d, %d, %s, %s ) , ";
				$params[] = $post['data_id'];
				$params[] = $d;
				$params[] = $result_type;
				$params[] = $post[$key];
				//
				if(isset($condition_line[$d])){
					$params[] = $condition_line[$d];
				}else{
					$params[] = '';
				}
			}
		}
		//
		if(!empty($insert_value)){
			$sql = "INSERT INTO `".OSDG_PLUGIN_DETAIL_TABLE_NAME."` ".$insert_into." VALUES ".rtrim($insert_value, " , ");
			$wpdb->query( $wpdb->prepare($sql, $params) );
		}

		return $return_array;

	}
	// 設問の更新
	public function write_diagnosis_question($post='', $id='0'){

		global $wpdb;
		// 処理
		foreach($post as $t => $pos){
			$set_data = '';
			$params = array();
			//
			foreach($pos as $key => $p){
				$set_data = "`question_".self::sql_escape($key)."`= %s , ";
				$params[] = $p;
			}
			//
			$sql = "UPDATE `".OSDG_PLUGIN_QUESTION_TABLE_NAME."` SET ".rtrim($set_data, ", ")." WHERE `data_id`= %d AND `delete_flag`='0' AND `sort_id`= %d";
			$params[] = $id;
			$params[] = $t;
			$wpdb->query( $wpdb->prepare($sql, $params) );
			unset($params);
		}

	}
	// 設問形式の処理
	private function condition_line($post=''){

		// 設問形式のとき
		if(!empty($post['diagnosis_type']) && $post['diagnosis_type']==1){
			$condition_line = array();
			//
			foreach($post['before_condition'] as $i => $line){
				$condition_detail_line = '';
				//
				foreach($line as $ln => $before){
					$after = $post['after_condition'][$i][$ln];
					$condition_detail_line .= $before.','.$after."\n";
				}
				$condition_line[$i] = rtrim($condition_detail_line, "\n");
			}

			return $condition_line;

		}else{

			return '';

		}

	}
	// 条件によって$pを変化させる
	private function post_premise_change($post='', $p='', $key=''){

		// 条件によって値を0にする
		if($post['display_flag']==0){
			switch($key){
				case 'result_type_flag': case 'result_page':
					$p = 0;
					break;
				case 'result_page_url': case 'form_class':
					$p = '';
					break;
			}
		}
		// 条件によって値を空にする
		if($key=='result_page_url' && !empty($p)){
			if($post['result_page']==0){
				$p = '';
			}
		}

		return $p;

	}
	//
	private function data_key($t, $key){

		$return_key = $t;

		if(stristr($key, "image")){
			$str = intval(str_replace("image", "", $key));
			$return_key = 1000 + $str;
		}

		return $return_key;

	}
	/*
	/*  指定したものを削除（削除フラグをたてる）
	*/
	public function delete_data($tbl, $key, $id){

		global $wpdb;
		$sql = "UPDATE `".self::sql_escape($tbl)."` SET `delete_flag`='1' WHERE `".self::sql_escape($key)."`= %d ";
		$params = array($id);
		$wpdb->query( $wpdb->prepare($sql, $params) );

	}
	/*
	*  基本処理
	*/
	// 条件にあてはまるデータの総数
	public function sql_count($tbl, $where=''){

		global $wpdb;
		$table = self::is_array_return($tbl);

		if(!empty($where)){
			$where = " WHERE ".$where;
		}

		$sql = "SELECT COUNT(*) FROM ".$table.$where;
		return $wpdb->get_var($wpdb->prepare($sql));

	}
	// テーブルの存在チェック
	public function show_table($tbl){

		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl));

	}
	// sqlを操作するファイルを読み込み、sqlを実行
	public function sql_performs($sql=''){

		global $osdg_sqlfile_check;
		// 既に読み込まれていないければファイル読み込み
		if($osdg_sqlfile_check!='1'){
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$GLOBALS['osdg_sqlfile_check'] = 1; // 読み込みチェック
		}

		return dbDelta($sql);

	}
	/*
	*  テーブル処理
	*/
	// プラグイン用のテーブルを新規作成
	public function newTable(){

		// テーブルを作成
		self::newDataTable();
		self::newDetailDataTable();
		self::newQuestionDataTable();

	}
	// プラグイン用のテーブルを削除
	public function deleteTable(){

		global $wpdb;
		$wpdb->query("DELETE FROM ".OSDG_PLUGIN_TABLE_NAME.";");
		$wpdb->query("DELETE FROM ".OSDG_PLUGIN_DETAIL_TABLE_NAME.";");
		$wpdb->query("DELETE FROM ".OSDG_PLUGIN_QUESTION_TABLE_NAME.";");

	}
	/*
	*  OSDG_PLUGIN_TABLE_NAME = 診断フォームデータ
	*  data_id データid、 form_title 診断フォームのタイトル、 form_title_flag タイトルを表示するか否か 1=表示（デフォルト）、
	*  form_input_label 入力フォームのラベル、 form_input_placeholder プレースホルダ、 form_submit_text 送信ボタンのテキスト、
	*  form_class　フォームのclass、 diagnosis_type 診断タイプ、 diagnosis_count 診断の設問数、
	*  default_pattern 全ての条件に当てはまらない場合に表示するパターン、 result_text 結果のテキスト（タグいり）、
	*  result_page、result_page_url 診断結果を同一ページか別ページに表示するのか、 result_type_flag 画像を使用するか否か
	*  display_flag 詳細条件、 form_header ヘッダ、 form_footer フッタ、
	*  after_header_flag 診断結果のヘッダーを切り替える場合は1、 after_footer_flag　診断結果のフッタ 同左、
	*      |_ after_header_flagが0の場合はform_headerが使用される
	*  form_after_header 診断結果のヘッダー（after_header_flag=1の時）、 form_after_footer 診断結果のフッター（同左）、
	*  post_authority 診断を利用できるユーザ、 delete_flag 削除フラグ、 create_time 作成日時、 update_time 更新日時、
	*/
	public function newDataTable(){

		$charset = defined("DB_CHARSET") ? DB_CHARSET : "utf8";
		$sql = "CREATE TABLE " .OSDG_PLUGIN_TABLE_NAME. " (\n".
				"`data_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n".
				"`form_title` text NOT NULL,\n".
				"`form_title_flag` int(1) NOT NULL DEFAULT '1',\n".
				"`form_input_label` varchar(255) NOT NULL,\n".
				"`form_input_placeholder` varchar(255) NOT NULL,\n".
				"`form_submit_text` varchar(255) NOT NULL,\n".
				"`form_class` varchar(255) NOT NULL,\n".
				"`diagnosis_type` int(1) NOT NULL DEFAULT '0',\n".
				"`diagnosis_count` int(3) NOT NULL DEFAULT '0',\n".
				"`default_pattern` int(100) NOT NULL DEFAULT '1',\n".
				"`result_text` text NOT NULL,\n".
				"`result_page` int(1) NOT NULL DEFAULT '0',\n".
				"`result_page_url` text NOT NULL,\n".
				"`result_type_flag` int(1) NOT NULL DEFAULT '0',\n".
				"`display_flag` int(1) NOT NULL DEFAULT '0',\n".
				"`form_header` text NOT NULL,\n".
				"`form_footer` text NOT NULL,\n".
				"`after_header_flag` int(1) NOT NULL DEFAULT '0',\n".
				"`after_footer_flag` int(1) NOT NULL DEFAULT '0',\n".
				"`form_after_header` text NOT NULL,\n".
				"`form_after_footer` text NOT NULL,\n".
				"`post_authority` int(1) NOT NULL DEFAULT '0',\n".
				"`delete_flag` int(1) NOT NULL DEFAULT '0',\n".
				"`create_time` datetime NOT NULL,\n".
				"`update_time` timestamp NOT NULL,\n".
				"PRIMARY KEY (`data_id`)\n".
			") ENGINE = MyISAM DEFAULT CHARSET=".$charset." AUTO_INCREMENT=1 \n";
		self::sql_performs($sql);

	}
	/*
	*  OSDG_PLUGIN_DETAIL_TABLE_NAME = 診断フォームデータ詳細
	*  detail_data_id 詳細データid、 data_id　データid、 pattern_id パターンid、
	*  result_type 結果タイプ 0=テキスト 1=画像、 result_text 結果表示に使用するテキスト、
	*  condition 表示条件、 delete_flag 削除フラグ、
	*/
	public function newDetailDataTable(){

		$charset = defined("DB_CHARSET") ? DB_CHARSET : "utf8";
		$sql = "CREATE TABLE " .OSDG_PLUGIN_DETAIL_TABLE_NAME. " (\n".
				"`detail_data_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n".
				"`data_id` bigint(20) NOT NULL DEFAULT '0',\n".
				"`pattern_id` bigint(20) NOT NULL DEFAULT '0',\n".
				"`result_type` int(1) NOT NULL DEFAULT '0',\n".
				"`result_text` text NOT NULL,\n".
				"`condition` text NOT NULL,\n".
				"`delete_flag` int(1) NOT NULL DEFAULT '0',\n".
				"PRIMARY KEY (`detail_data_id`)\n".
			") ENGINE = MyISAM DEFAULT CHARSET=".$charset." AUTO_INCREMENT=1 \n";
		// SQL実行
		self::sql_performs($sql);

	}
	/*
	*  OSDG_PLUGIN_QUESTION_TABLE_NAME = 診断フォーム設問データ
	*  question_id データid、 data_id　フォームデータid、 sort_id 表示順id、
	*  question_text 設問文、 question_choice 設問の選択肢、 question_point 設問の点数、
	*  delete_flag 削除フラグ、
	*/
	public function newQuestionDataTable(){

		$charset = defined("DB_CHARSET") ? DB_CHARSET : "utf8";
		$sql = "CREATE TABLE " .OSDG_PLUGIN_QUESTION_TABLE_NAME. " (\n".
				"`question_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n".
				"`data_id` bigint(20) NOT NULL DEFAULT '0',\n".
				"`sort_id` bigint(20) NOT NULL DEFAULT '0',\n".
				"`question_text` text NOT NULL,\n".
				"`question_choice` text NOT NULL,\n".
				"`question_point` text NOT NULL,\n".
				"`delete_flag` int(1) NOT NULL DEFAULT '0',\n".
				"PRIMARY KEY (`question_id`)\n".
			") ENGINE = MyISAM DEFAULT CHARSET=".$charset." AUTO_INCREMENT=1 \n";
		// SQL実行
		self::sql_performs($sql);

	}

}
?>