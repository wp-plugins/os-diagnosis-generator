<?php
//
function osdgTimezoneSet(){
	$timezone = osdgTimezoneGet(); // PHP側のデフォルト取得
	//
	if($option_data = get_option(OSDG_PLUGIN_DATA_NAME)){
		// データがあれば
		if(isset($option_data['timezone'])){
			date_default_timezone_set($option_data['timezone']);
		}else{
			date_default_timezone_set($timezone);
		}
		unset($option_data);
	}else{
		date_default_timezone_set($timezone);
	}
}
// 設定されているタイムゾーンを取得 php.ini優先
function osdgTimezoneGet(){
	if(ini_get('date.timezone')){
		$timezone = ini_get('date.timezone');
	}else{
		if(date_default_timezone_get()){
			$timezone = date_default_timezone_get();
		}else{
			$timezone = 'UTC';
		}
	}
	return $timezone;
}
// プラグインが有効化されたときに実行する
function osdgActivationPlugin(){
	// 過去にに有効化されている場合
	if(get_option(OSDG_PLUGIN_TABLE_VERSION_NAME)){

	}else{ // 初めて有効化した場合
		DiagnosisAdmin::firstOption();
	}
	// テーブルが存在しなければ作成
	if(!DiagnosisSqlClass::show_table(OSDG_PLUGIN_TABLE_NAME)){
		DiagnosisSqlClass::newDataTable();
	}
	if(!DiagnosisSqlClass::show_table(OSDG_PLUGIN_DETAIL_TABLE_NAME)){
		DiagnosisSqlClass::newDetailDataTable();
	}
	if(!DiagnosisSqlClass::show_table(OSDG_PLUGIN_QUESTION_TABLE_NAME)){
		DiagnosisSqlClass::newQuestionDataTable();
	}
}
if(function_exists('register_activation_hook')){
	register_activation_hook(OSDG_PLUGIN_FILE, 'osdgActivationPlugin');
}
?>
