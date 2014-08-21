<?php
/*
Plugin Name: 診断ジェネレータ作成プラグイン
Plugin URI: http://olivesystem.jp/lp/plugin-dg
Description: WordPressで診断ジェネレータ（診断サイト、占いサイト）を作成できるプラグインです
Version: 1.0.2
Author: OLIVESYSTEM（オリーブシステム）
Author URI: http://www.olivesystem.com/
*/
if(!isset($wpdb)){
	global $wpdb;
}
// 現在のプラグインバージョン
define('OSDG_PLUGIN_VERSION','1.0.2');
// 現在のテーブルバージョン
define('OSDG_PLUGIN_TABLE_VERSION','1.0');
// DBにデータを保存する項目名
define('OSDG_PLUGIN_VERSION_NAME','os_diagnosis_generator_PluginVersion');
define('OSDG_PLUGIN_TABLE_VERSION_NAME','os_diagnosis_generator_PluginTableVersion');
define('OSDG_PLUGIN_DATA_NAME','os_diagnosis_generator_Plugin');
// テーブル名
define('OSDG_PREFIX', $wpdb->prefix); // プレフィックス
define('OSDG_PLUGIN_TABLE_NAME', OSDG_PREFIX.'os_diagnosis_generator_data');
define('OSDG_PLUGIN_DETAIL_TABLE_NAME', OSDG_PREFIX.'os_diagnosis_generator_detail_data');
define('OSDG_PLUGIN_QUESTION_TABLE_NAME', OSDG_PREFIX.'os_diagnosis_generator_question_data');
// このファイル
define('OSDG_PLUGIN_FILE', __FILE__);
// プラグインのディレクトリ
define('OSDG_PLUGIN_DIR', plugin_dir_path(__FILE__));
// テキストメインのPHPファイルをいれているディレクトリ
define('OSDG_PLUGIN_INCLUDE_FILES', OSDG_PLUGIN_DIR.'include_files');
// グローバル変数
$osdg_user_data = '';
$osdg_sqlfile_check = '';
$osdg_option_data = '';
$osdg_cache_data = '';
// 関数
include OSDG_PLUGIN_DIR."function.php";
// 時刻のタイムゾーンを設定
osdgTimezoneSet();
// 共通class
include OSDG_PLUGIN_DIR."class/messageClass.php";
include OSDG_PLUGIN_DIR."diagnosisClass.php";
include OSDG_PLUGIN_DIR."class/validationClass.php";
include OSDG_PLUGIN_DIR."class/sqlClass.php";
include OSDG_PLUGIN_DIR."class/resultClass.php";
// 表示側
include OSDG_PLUGIN_DIR."diagnosisViewClass.php";
$diagnosisViewClass = new DiagnosisView();
// 管理画面側
include OSDG_PLUGIN_DIR."diagnosisAdminClass.php";
$diagnosisAdminClass = new DiagnosisAdmin();

?>
