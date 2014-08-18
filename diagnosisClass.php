<?php
class DiagnosisClass {

	public function __construct(){

		add_action('plugins_loaded', array('DiagnosisClass', 'plugin_get_option'));
		// エラー回避のためプラグイン読み込み時にアクション
		add_action('plugins_loaded', array('DiagnosisClass', 'action_level'));
		// ヘッダの処理
		add_action('wp_head', array('DiagnosisClass', 'action_head'));

	}
	/*
	*  オプション取得
	*/
	public function plugin_get_option(){

		$GLOBALS['osdg_option_data'] = get_option(OSDG_PLUGIN_DATA_NAME);

	}
	/*
	*  ユーザデータ、権限の取得
	*/
	public function action_level(){

		// ログインしてるとき、ユーザデータを取得し、グローバル変数へ
		if(is_user_logged_in()){

			global $current_user;
			get_currentuserinfo();
			$arr = array();

			foreach($current_user->data as $key => $user){

				if($key!='user_pass' && $key!='user_activation_key'){
					$arr[$key] = $user;
				}

			}

			$arr['level'] = $current_user->roles[0];
			$GLOBALS['osdg_user_data'] = $arr;

		}else{

			$GLOBALS['osdg_user_data'] = array('level'=>'guest');

		}

	}
	/*
	*  独自リダイレクト（先にヘッダが送信されているとリダイレクトできないため）
	*/
	public function os_redirect($url=''){

		if(self::header_check()==TRUE){
			print '<meta http-equiv="refresh" content="0;URL='.$url.'" />';
		}else{
			wp_safe_redirect($url);
		}

	}
	// ヘッダーが送信されているかチェック
	public function header_check(){

		if(headers_sent($filename, $linenum)){
			//print_r(headers_list());
			//echo "$filename の $linenum 行目でヘッダがすでに送信されています。\n";
			return TRUE;
		}else{
			return FALSE;
		}

	}
	// URLにパラメータ付加
	public function url_plus($params='', $url=''){

		if(empty($url)){
			$now_url = get_permalink();
		}else{
			$now_url = $url;
		}
		$plus = self::url_plus_params($params);
		//
		if(stristr($now_url, "?")){
			return $now_url."&".$plus;
		}else{
			return $now_url."?".$plus;
		}

	}
	private function url_plus_params($params=''){

		$return_data = '';

		if(is_array($params)){
			foreach($params as $key => $p){
				$return_data .= self::h($key).'='.self::h($p).'&';
			}
			//
			$return_data = rtrim($return_data, "&");
		}else{
			$return_data = self::h($params);
		}

		return $return_data;

	}
	/*
	*  一時データ処理
	*/
	// 一時データ保存
	public function action_cache_write($data='', $key=''){

		// キーがあれば
		if(!empty($key)){
			$cache_data = array();
			$cache_data[$key] = $data;
		}else{
			$cache_data = $data;
		}
		// 配列ならそのまま
		if(is_array($data)){
			$GLOBALS['osdg_cache_data'] = $cache_data;
		}else{
			$GLOBALS['osdg_cache_data']['data'] = $cache_data;
		}

	}
	// 一時データ読み込み
	public function action_cache_read($key='', $str=''){

		if(!empty($key)){
			if(!empty($str)){
				return $GLOBALS['osdg_cache_data'][$key][$str];
			}else{
				return $GLOBALS['osdg_cache_data'][$key];
			}
		}else{
			return $GLOBALS['osdg_cache_data']['data'];
		}

	}
	// 一時データ削除
	public function action_cache_delete($key='', $str=''){

		if(!empty($key)){
			if(!empty($str)){
				if(isset($GLOBALS['osdg_cache_data']) && isset($GLOBALS['osdg_cache_data'][$key]) && isset($GLOBALS['osdg_cache_data'][$key][$str])){
					unset($GLOBALS['osdg_cache_data'][$key][$str]);
				}
			}else{
				if(isset($GLOBALS['osdg_cache_data']) && isset($GLOBALS['osdg_cache_data'][$key])){
					unset($GLOBALS['osdg_cache_data'][$key]);
				}
			}
		}else{
			if(isset($GLOBALS['osdg_cache_data']) && isset($GLOBALS['osdg_cache_data']['data'])){
				unset($GLOBALS['osdg_cache_data']['data']);
			}
		}

	}
	/*
	*  エスケープ
	*/
	// SQLエスケープ
	public function sql_escape($str=''){

		$return_data = '';

		if(isset($str)){
			$return_data = esc_sql($str);
		}

		return $return_data;

	}
	// htmlエスケープ
	public function h($str=''){

		$return_data = '';

		if(is_array($str)){
			$return_data = array();
			foreach($str as $key => $st){
				$key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
				$return_data[$key] = self::h($st);
			}
		}else{
			$return_data = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		}

		return $return_data;

	}
	// htmlエスケープのデコ－ド
	public function h_dec($str=''){

		$return_data = '';

		if(is_array($str)){
			$return_data = array();
			foreach($str as $key => $st){
				$return_data[$key] = self::h_entity_decode($st);
			}
		}else{
			$return_data = self::h_entity_decode($str);
		}

		return $return_data;

	}
	// 上記で使用
	private function h_entity_decode($str){

		$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
		$str = self::h_entity_decode_change($str);
		return $str;

	}
	// 上記で使用
	private function h_entity_decode_change($str){

		$check_arr = array('\"', "\&quot;", "\'", "\&#039;");
		$change_arr = array('"', '"', "'", "'");
		$str = str_replace($check_arr, $change_arr, $str);
		return $str;

	}
	// POSTをエスケープして返す
	public function post_escape(){

		$arr = array();

		foreach($_POST as $key => $p){

			if(is_array($p)){
				$arr[$key] = self::post_escape_arr($p);
			}else{
				$arr[$key] = self::h($p);
			}

		}

		return $arr;

	}
	private function post_escape_arr($arr){

		foreach($arr as $key => $p){
			if(is_array($p)){
				$arr[$key] = self::post_escape_arr($p);
			}else{
				$arr[$key] = self::h($p);
			}
		}

		return $arr;

	}
	// GETをエスケープして返す
	public function get_escape(){

		$arr = array();

		foreach($_GET as $key => $g){

			$arr[$key] = self::h($g);

		}

		return $arr;

	}
	// $_GETをurl形式にする
	public function get_param($type=''){

		$i = 0;
		$_return = '';

		foreach($_GET as $key => $g){

			if(empty($type) && $i==0){
				$param = "?";
			}else{
				$param = "&";
			}

			$_return .= $param.self::h($key)."=".self::h($g);
			$i++;

		}

		return $_return;

	}
	// データをarrayで整理
	public function arrayData($data=''){

		$arr = array();

		foreach($data as $key => $d){

			if($key=='Submit' || $key=='submit' || $key=='option'){

			}else{
				$arr[$key] = self::sql_escape($d);
			}

		}

		return $arr;

	}
	// 配列なら処理（$cols,$tblで使用）
	public function is_array_return($str='', $comma=''){

		$return_data = '';

		// 配列なら処理
		if(is_array($str)){

			foreach($str as $val){
				if($comma=='1'){
					$return_data .= $val.", ";
				}elseif($comma=='2'){
					$return_data .= "'".$val."', ";
				}else{
					$return_data .= '`'.$val.'`, ';
				}
			}

			$return_data = rtrim($return_data, ", ");

		}else{

			if($str=='*'){
				$return_data = $str;
			}else{
				if($comma=='1'){
					$return_data .= $val.", ";
				}elseif($comma=='2'){
					$return_data .= "'".$val."', ";
				}else{
					$return_data = '`'.$str.'`';
				}
			}

		}

		return $return_data;

	}
	// 指定したkeyのPOSTをecho
	public function post_set($key='', $type=''){

		if(stristr($key, '.')){ // キーが配列なら（.）で判断
			// POSTしてるなら処理
			if(!empty($_POST)){
				$post_arr = $_POST;
				$key_ex = explode(".", $key);
				$first_key = $key_ex[0];
				//
				if(isset($_POST[$first_key])){
					$ct = count($key_ex);
					$break = $ct - 1;
					// 処理
					if(1<$ct){
						for($i=0; $i<$ct; $i++){
							$key = $key_ex[$i];
							//
							if($i==0){
								if(isset($post_arr[$key])){
									$post = $post_arr[$key];
								}
							}elseif($i<$break){
								if(isset($post[$key])){
									$post = $post[$key];
								}
							}
						}
					}else{
						$post = $_POST;
					}
				}
			}
		}else{
			$post = $_POST;
		}

		if(isset($post) && isset($post[$key])){
			if($type=='1'){
				return self::h($post[$key]);
			}else{
				switch($type){
					case '3':
						echo self::h_entity_decode_change($post[$key]);
						break;
					case '2':
						echo $post[$key];
						break;
					default:
						echo self::h($post[$key]);
				}
			}
		}else{
			if(isset($GLOBALS['osdg_cache_data']) && isset($GLOBALS['osdg_cache_data']['data'])){
				$data = $GLOBALS['osdg_cache_data']['data'];
			}else{
				$data = '';
			}
			return self::data_set($data, $key, $type);
		}

	}
	// 指定したkeyのDataをecho
	public function data_set($data='', $key='', $type=''){

		if(stristr($key, '.')){ // キーが配列なら（.）で判断
			$post_arr = $data;
			$key_ex = explode(".", $key);
			$first_key = $key_ex[0];
			//
			if(isset($data[$first_key])){
				$ct = count($key_ex);
				$break = $ct - 1;
				// 処理
				if(1<$ct){
					for($i=0; $i<$ct; $i++){
						$key = $key_ex[$i];
						//
						if($i==0){
							if(isset($post_arr[$key])){
								$post = $post_arr[$key];
							}
						}elseif($i<$break){
							if(isset($post[$key])){
								$post = $post[$key];
							}
						}
					}
				}else{
					$post = $data;
				}
			}
		}else{
			$post = $data;
		}

		if(isset($post[$key])){
			if($type=='1'){
				return self::h($post[$key]);
			}else{
				$value = $post[$key];
				if(!is_array($value)){ // 配列じゃなければ
					switch($type){
						case '3':
							echo self::h_entity_decode_change($post[$key]);
							break;
						case '2':
							echo $post[$key];
							break;
						default:
							echo self::h($post[$key]);
					}
				}
			}
		}else{
			return '';
		}

	}
	/*
	*  ヘッダの処理
	*/
	public function action_head(){

		global $osdg_option_data;
		//
		if(isset($osdg_option_data['license'])){
			$license = self::h($osdg_option_data['license']);
		}else{
			$license = "free";
		}
		$text = '<meta name="generator" content="os-diagnosis-generator" />'."\n";
		$text .= '<meta name="osdg-id" content="'.self::h($license).'" />'."\n";
		echo $text;

	}
	// ライセンス
	public function osdgLicense($type=''){

		global $osdg_option_data;
		$data = DiagnosisResultClass::encodeData();
		//
		if(isset($osdg_option_data) && isset($osdg_option_data['license'])){
			$_return = DiagnosisResultClass::licenseCheck($osdg_option_data['license']);
			//
			if($_return=='free'){
				if($type==1){
					return urldecode($data);
				}else{
					print urldecode($data);
				}
			}
		}else{
			if($type==1){
				return urldecode($data);
			}else{
				print urldecode($data);
			}
		}

	}

}
?>