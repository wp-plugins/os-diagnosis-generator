<?php
if(class_exists('DiagnosisClass')){
// 診断処理など操作するcalss
class DiagnosisResultClass extends DiagnosisClass {

	public function __construct(){

		parent::__construct();

	}
	/*
	*  結果表示データ処理
	*/
	public function result_data_arrangement($get='', $data=''){

		$result_text = $data['result_text'];
		$name = self::h(urldecode($get['osdgn']));
		$list = explode(",", self::h($get['osdgl']));
		$img_list = explode(",", self::h($get['osdgimg']));
		// それぞれ置き換え処理
		for($i=0; $i<11; $i++){
			if($i==0){
				$check = "[Name]"; // 名前
				$change = $name;
			}else{
				$check = "[Text{$i}]"; // Text系
				$str = "text{$i}";
				// データ取得
				if(isset($data[$str])){
					$line = explode("\n", trim($data[$str])); // 1行ずつ配列
					$t = $i - 1;
					// どの行にするか
					if(isset($list[$t])){
						$ln = $list[$t] - 1;
					}else{
						$ln = 0;
					}
					//
					$change = $line[$ln];
				}else{
					$change = '[error]';
				}
			}
			//
			if(stristr($result_text, $check)){
				$result_text = str_replace($check, trim($change), $result_text);
			}
		}
		// HTML用のタグを変換 ======================================= start
		// h1～h5タグ
		if(preg_match_all('/(\[|\[\/)h([1-5])\]/i', $result_text, $tag_match)){
			foreach($tag_match[0] as $key => $val){
				if(!empty($tag_match[2]) && !empty($tag_match[2][$key])){
					if(stristr($val, "/")){
						$replace_str = '</h'.trim($tag_match[2][$key]).'>';
					}else{
						$replace_str = '<h'.trim($tag_match[2][$key]).'>';
					}
					$result_text = str_replace($val, $replace_str, $result_text);
				}
			}
			unset($tag_match);
		}
		// カラータグ
		// 開始タグ
		if(preg_match_all('/\[color:([a-zA-Z0-5#]+)\]/i', $result_text, $tag_match)){
			foreach($tag_match[0] as $key => $val){
				if(!empty($tag_match[1]) && !empty($tag_match[1][$key])){
					$color = $tag_match[1][$key];
					$result_text = str_replace($val, '<span style="color:'.$color.';">', $result_text);
				}
			}
			unset($tag_match);
		}
		// 閉じタグ
		if(preg_match_all('/\[\/color\]/i', $result_text, $tag_match)){
			foreach($tag_match[0] as $key => $val){
				$result_text = str_replace($val, '</span>', $result_text);
			}
			unset($tag_match);
		}
		// フォントサイズ
		// 開始タグ
		if(preg_match_all('/\[size:([0-9]+)\]/i', $result_text, $tag_match)){
			foreach($tag_match[0] as $key => $val){
				if(!empty($tag_match[1]) && !empty($tag_match[1][$key])){
					$size = $tag_match[1][$key];
					$result_text = str_replace($val, '<span style="font-size:'.$size.'px;">', $result_text);
				}
			}
			unset($tag_match);
		}
		// 閉じタグ
		if(preg_match_all('/\[\/size\]/i', $result_text, $tag_match)){
			foreach($tag_match[0] as $key => $val){
				$result_text = str_replace($val, '</span>', $result_text);
			}
			unset($tag_match);
		}
		// HTML用のタグを変換 ======================================= end
		// 画像
		if(!empty($data['display_flag']) && !empty($data['result_type_flag'])){
			$image_data = '';
			$ct = count($img_list);
			// 処理
			for($d=0; $d<$ct; $d++){
				$i = $d + 1;
				$str = "image{$i}";
				// データ取得
				if(isset($data[$str])){
					$line = explode("\n", trim($data[$str])); // 1行ずつ配列
					// どの行にするか
					if(isset($img_list[$d])){
						$ln = $img_list[$d] - 1;
					}else{
						$ln = 0;
					}
					//
					$img_url = trim($line[$ln]);
					$image_data .= '<p class="result-img rimg'.$d.'"><img src="'.$img_url.'" alt="" /></p>'."\n";
				}
			}
			$result_text = $image_data.$result_text;
		}

		return $result_text;

	}
	// 診断処理 //////////////////////////// =>
	/*
	*  システム側で処理する診断
	*/
	// システムで自動で判断
	public function result_system($result_text='', $result_image='', $arr=array('hash'=>'', 'len'=>'')){

		$split = self::split_array($arr['hash']);
		$len = $arr['len'];
		$word_rank = self::word_many_rank($split['word']);
		$number_data = array();
		$number_img_data = array();
		$eisu = array();
		//
		for($r=0; $r<2; $r++){
			if($r==0){ // Text系の処理
				$rdata = $result_text;
			}else{ // Image系の処理
				$rdata = $result_image;
			}
			//
			if(!empty($rdata['count'])){ // あれば処理
				for($i=0; $i<$rdata['count']; $i++){
					$t = $i + 1;
					$small_count = $rdata['text'][$t]['count'];
					$eisu = self::eisu_array($small_count);
					$text_data = $rdata['text'][$t]['data'];
					//
					switch($t){
						case 1:
							$word = $split['word'][0];
							break;
						// Text2～5の計算パターン
						case 2: case 3: case 4: case 5:
							switch($t){
								case 3: $m = 10; break;
								case 4: $m = 20; break;
								case 5: $m = 30; break;
								default: $m = 1; // t=2
							}
							$str = $len - $m;
							$str = abs($str);
							$word = $split['word'][$str];
							break;
						// Text6～10の計算パターン
						case 6: case 7: case 8: case 9: case 10:
							switch($t){
								case 6: $r = 2; break;
								case 7: $r = 3; break;
								case 8: $r = 4; break;
								case 9: $r = 5; break;
								default: $r = 6; // t=10
							}
							if(isset($word_rank[$r])){ // 存在すれば
								$word = $word_rank[$r];
							}else{
								$word = $word_rank[1];
							}
							break;
					}
					//
					if(isset($eisu[$word])){ // 存在すれば
						$key = $eisu[$word];
					}else{ // 存在しなければ
						$key = mt_rand(1, $small_count);
					}
					//
					if($r==0){
						$number_data[] = $key;
					}else{
						$number_img_data[] = $key;
					}
				}
			}
		}

		$result_data = implode(",", $number_data);
		$result_data_img = implode(",", $number_img_data);

		return array('line'=>$result_data, 'img'=>$result_data_img);

	}
	// 文字が何回でるか
	public function word_many_rank($split_word=''){

		$arr = array();
		$return_data = array();
		$i = 1;
		//
		foreach($split_word as $sp){
			if(isset($arr[$sp])){
				$arr[$sp] = $arr[$sp] + 1;
			}else{
				$arr[$sp] = 1;
			}
		}
		// ソート
		arsort($arr);
		//
		foreach($arr as $key => $a){
			$return_data[$i] = $key;
			$i++;
		}

		return $return_data;

	}
	// 指定したカウント数まで1から順番に数字をいれていく
	public function eisu_array($count='0'){

		$return_data = array();
		$i = 1;
		$eisu = array(
			'a','b','c','d','e','f','g','h','i','j','k','m','n','l','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9'
		);
		//
		foreach($eisu as $e){
			$return_data[$e] = $i;
			//
			if($i==$count){
				$i = 1;
			}else{
				$i++;
			}
		}

		return $return_data;

	}
	/*
	*  設問で診断を処理する
	*/
	public function result_qsystem($post, $data){

		$result_data = '';
		$img_result_data = '';
		$total = 0;

		if(!empty($post['question']) && !empty($data['question'])){

			$point = $data['question'];

			foreach($post['question'] as $key => $q){
				// 点数を足していく
				if(isset($point[$key]) && isset($point[$key]['point'])){
					$point_data = $point[$key]['point'];
					$point_line = explode("\n", trim($point_data));
					//
					if(isset($point_line[$q])){
						$points = $point_line[$q];
					}else{
						$points = 0;
					}
				}else{
					$points = 0;
				}
				//
				$total = $total + $points;
			}
			// 点数から何行目か取得
			if(!empty($data['before_condition']) && !empty($data['after_condition'])){
				//
				foreach($data['before_condition'] as $key => $cond){
					$line = '';
					//
					if(isset($data['after_condition'][$key])){
						$after = $data['after_condition'][$key];
					}else{ // ない場合
						$after = $data['before_condition'][$key];
					}
					//
					foreach($cond as $k => $c){
						if(isset($after[$k])){
							$af = $after[$k];
						}else{ // ない場合
							$af = 100;
						}
						//
						if(($c<$total || $c==$total) && $total<$af){
							$line = $k;
							break;
						}
					}
					//
					if(999<$key){
						$img_result_data .= $line.',';
					}else{
						$result_data .= $line.',';
					}
				}
				//
				$result_data = rtrim($result_data, ",");
				$img_result_data = rtrim($img_result_data, ",");
			}else{
				$result_data = '1';
			}

		}

		return array('line'=>$result_data, 'img'=>$img_result_data);

	}
	// 診断処理 //////////////////////////// <=
	/*
	*  診断のための整理
	*/
	// Textデータの整理
	public function result_text_arr($data='', $type=''){

		$return_data = array();
		$text_data = array();

		for($i=1; $i<11; $i++){
			if($type=='image'){
				$key = 'image'.$i;
			}else{
				$key = 'text'.$i;
			}
			//
			if(!empty($data[$key])){
				$data_ex = explode("\n", trim($data[$key]));
				$text_data[$i]['data'] = $data_ex;
				$text_data[$i]['count'] = count($data_ex);
			}
		}

		$counter = count($text_data);

		return array('count'=>$counter, 'text'=>$text_data);

	}
	/*
	*  文字の処理
	*/
	// 1文字ずつ配列にする
	public function split_array($word=''){

		if(!empty($word)){
			$arr = array();
			$len = strlen($word); // 長さ
			// 1文字ずつ配列
			for($i=0; $i<$len; $i++){
				$mozi = substr($word, $i, 1);
				array_push($arr, $mozi);
			}
		}

		return array('word'=>$arr, 'count'=>$len);

	}
	// 名前をハッシュ化し、ハッシュ化したものと長さ
	public function hash_len($name=''){

		$hash = sha1($name);
		$len = strlen($hash);

		return array('hash'=>$hash, 'len'=>$len);

	}
	/*
	*  ライセンスで処理
	*/
	public function licenseCheck($license=''){

		$return_data = "free";
		// チェック
		if(stristr($license, "osdg-")){
			$ls_ex = explode("-", trim($license));
			//
			if(!empty($ls_ex[1]) && preg_match("/([a-zA-Z0-9]+)/", $ls_ex[1])){
				if(!empty($ls_ex[2])){
					switch($ls_ex[2]){
						case 'ware':
							$return_data = "ware";
							break;
						case 'pro':
							$return_data = "pro";
							break;
					}
				}
			}
		}

		return $return_data;

	}
	//
	public function encodeData(){

		$data = '%0A%09%09%3Cdiv+class%3D%22plugin-copyright%22%3E%0A%09%09%09%3Ca+href%3D%22http%3A%2F%2Folivesystem.jp%2Flp%2Fplugin-dg%22+target%3D%22_blank%22+rel%3D%22nofollow%22%3E%E8%A8%BA%E6%96%AD%E3%82%B8%E3%82%A7%E3%83%8D%E3%83%AC%E3%83%BC%E3%82%BF%E4%BD%9C%E6%88%90%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%3C%2Fa%3E%0A%09%09%3C%2Fdiv%3E%0A%09%09';
		return $data;

	}

}}
?>