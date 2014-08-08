<?php
// バリデーションcalss
class DiagnosisValidationClass extends DiagnosisClass {

	public function __construct(){

		parent::__construct();

	}
	/*
	*  バリデーションのエラーがあるかどうか
	*/
	public function validates($validation=''){

		$return_data = 1;

		if(!empty($validation)){
			foreach($validation as $val){
				if(!empty($val)){
					$return_data = 0;
				}
			}
		}

		return $return_data;

	}
	/*
	*  バリデーションメッセージの修正
	*/
	public function validates_message($validation='', $array=array()){

		foreach($validation as $vkey => $valid){
			if(!empty($valid['error'])){
				foreach($array as $key => $arr){
					if(stristr($valid['text'], $key)){
						$validation[$vkey]['text'] = str_replace($key, $arr, $valid['text']);
					}
				}
			}
		}

		return $validation;

	}
	/*
	*  メッセージをpタグで囲む
	*/
	public function pmessage($validate){

		$message = '';

		foreach($validate as $val){
			$message .= '<p>'.$val['text'].'</p>';
		}

		return $message;

	}
	/*
	*  エラーがあればそれをJavascriptでcssの色を変える
	*/
	public function js_error_css($validate){

		$js = "<script>\nj(document).ready( function(){\n";

		foreach($validate as $key => $val){
			$js .= "\t j('#".$key."').css('border', '1px solid red');\n";
		}

		$js .= "});\n</script>\n";

		return $js;

	}
	/*
	*  バリデーションルール
	*/
	public function validation_rule($post='', $key='', $rule=''){

		$validation_data = array();
		$rule_arr = array();
		$str1 = '';
		$str2 = '';

		if(is_array($rule)){ // 配列ならそのまま
			$rule_arr = $rule;
		}else{
			$rule_arr = array($rule);
		}
		// チェック
		foreach($rule_arr as $r){
			if(is_array($r)){ // 更に配列なら
				$first = current($r); // 1番目の値
				//
				if(!empty($first)){
					// 2番目の値
					if($second = next($r)){
						$str1 = $second;
					}else{
						$second = 0;
					}
					// 末尾の値
					if($end = end($r)){
						$str2 = $end;
					}else{
						$end = 0;
					}
					//
					switch($first){
						case 'number':
							$return_rule = self::validation_number($post, $second, $end);
							break;
					}
					$rkey = $first;
				}
			}else{
				switch($r){
					case 'empty': case 'select-empty':
						$return_rule = self::validation_empty($post);
						break;
					case 'z-empty':
						$return_rule = self::validation_zero_or_empty($post);
						break;
					case 'numeric':
						$return_rule = self::validation_numeric($post);
						break;
				}
				$str1 = '';
				$str2 = '';
				$rkey = $r;
			}
			// エラーなら
			if($return_rule==1){
				$validation_data[$key]['error'] = $return_rule;
				$validation_data[$key]['rule'] = $rkey;
				$validation_data[$key]['text'] = self::validation_error_message($key, $rkey, $str1, $str2);
			}
			unset($return_rule);
		}

		return $validation_data;

	}
	// 空かどうか
	private function validation_empty($post=''){

		if(empty($post)){ // 空なら問題あり
			return 1;
		}else{
			return 0;
		}

	}
	// 0か空かどうか
	private function validation_zero_or_empty($post=''){

		if(empty($post)){ // 空なら
			if(is_numeric($post)){ // 0なら
				return 0;
			}else{ // 0でなければ問題あり
				return 1;
			}
		}else{
			return 0;
		}

	}
	// 数字かどうか
	private function validation_numeric($post=''){

		if(is_numeric($post)){ // 数値なら問題なし
			return 0;
		}else{
			return 1;
		}

	}
	// 指定した文字数内か
	private function validation_number($post='', $start='', $end=''){

		$start_error = 0;
		$end_error = 0;
		$count = mb_strlen($post);
		// 開始文字数
		if(!empty($start)){
			if($start<$count || $count==$start){
				$start_error = 0;
			}else{
				$start_error = 1;
			}
		}
		// 終了文字数
		if(!empty($end)){
			if($count<$end || $count==$end){
				$end_error = 0;
			}else{
				$end_error = 1;
			}
		}
		//
		if(!empty($end_error) || !empty($start_error)){ // どちらかが該当すれば
			return 1;
		}else{
			return 0;
		}

	}
	/*
	*  エラーメッセージ
	*/
	public function validation_error_message($key='', $rule='', $str1='', $str2=''){

		switch($rule){
			case 'empty':
				$message = $key.'は必須入力です。';
				break;
			case 'z-empty':
				$message = $key.'は必須入力です。';
				break;
			case 'select-empty':
				$message = $key.'を選択してください。';
				break;
			case 'numeric':
				$message = $key.'は数値のみ入力可能です。';
				break;
			case 'number':
				$text = '';
				if(!empty($str1)){
					$text .= $str1.'文字から';
				}
				if(!empty($str2)){
					$text .= $str2.'文字まで';
				}
				$message = $key.'は'.$text.'入力可能です。';
				break;
			default:
				$message = '';
		}

		return $message;

	}

}
?>