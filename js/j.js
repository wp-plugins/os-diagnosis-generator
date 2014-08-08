var j = jQuery.noConflict();
// メッセージ
function open_message(ids, vtext){
	var ok_text = '<span onclick="close_message(\''+ids+'\')" class="pointer-l">OK</span>';
	j('#'+ids).html('<div id="'+ids+'" class="green">'+vtext+ok_text+'</div>');
}
// メッセージを閉じる
function close_message(ids){
	j('#'+ids+'').html('<div id="'+ids+'"></div>');
}
// 要素を表示
function display_block(ids){
	j('#'+ids+'').css('display', 'block');
}
// 要素を非表示
function display_none(ids){
	j('#'+ids+'').css('display', 'none');
}
// クリア
function text_clear(ids){
	j('#'+ids+'').val('');
}