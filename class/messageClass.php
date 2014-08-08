<?php
// メッセージを操作するcalss
class DiagnosisMessageClass {

	public function updateMessage($type=''){

		$return_data = '';

		if(isset($_GET['msg'])){
			$message = explode(",", rtrim($_GET['msg'], ","));
			//
			foreach($message as $m){
				if($type==1){
					$return_data .= self::_viewUpdateMessage($m);
				}else{
					$return_data .= self::_updateMessage($m);
				}
			}
		}

		return $return_data;

	}
	// メッセージ
	public function _updateMessage($msg=''){

		$return_data = '';

		switch($msg){

			case "ok":
				$return_data .= "成功しました<br />";
				break;
			case "error":
				$return_data .= "失敗しました<br />";
				break;
			case "format-ok":
				$return_data .= "初期化しました<br />";
				break;
			case "format-error":
				$return_data .= "初期化に失敗しました<br />";
				break;
			case "insert-ok":
				$return_data .= "新規作成しました<br />";
				// 追加文
				if($_GET['page']=='diagnosis-generator-new.php' && !empty($_GET['id'])){
					$return_data .= "idは".DiagnosisClass::h($_GET['id'])."です。<a href=\"?page=diagnosis-generator-write.php&write_id=".DiagnosisClass::h($_GET['id'])."\">こちら</a>から編集できます。<br />診断を表示するには、表示したいページにショートコードを挿入してください。";
				}
				break;
			case "insert-ng":
				$return_data .= "新規作成に失敗しました<br />";
				break;
			case "update-ok":
				$return_data .= "更新に成功しました<br />";
				break;
			case "update-ng":
				$return_data .= "更新に失敗しました<br />";
				break;
			case "write-ok":
				$return_data .= "編集に成功しました<br />";
				break;
			case "write-ng":
				$return_data .= "編集に失敗しました<br />";
				break;
			case "delete-ok":
				$return_data .= "削除に成功しました<br />";
				break;
			case "delete-ng":
				$return_data .= "削除に失敗しました<br />";
				break;
			case "write-user-ng":
				$return_data .= "編集権限のないユーザです<br />";
				break;

		}

		return $return_data;

	}
	//
	public function _viewUpdateMessage($msg=''){

		$return_data = '';

		switch($msg){

			case "error":
				$return_data .= "失敗しました<br />";
				break;
			case "dg-error":
				$return_data .= "診断に失敗失敗しました<br />";
				break;

		}

		return $return_data;

	}

}
?>