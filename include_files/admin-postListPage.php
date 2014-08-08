<?php
if(class_exists('DiagnosisAdmin')){
	if(empty($data)){
		$message .= '<p>データがありません。<a href="?page=diagnosis-generator-new.php">こちら</a>から新規作成してください。</p>';
	}
?>

<script>
function view_div(ids, str){
	if(str==1){
		j('#'+ids).css('display', 'block');
	}
	else{
		j('#'+ids).css('display', 'none');
	}
}
</script>

	<div id="diagnosis-plugin">
		<div class="diagnosis-wrap">
			<h2>診断フォーム一覧</h2>
			<div class="diagnosis-contents">
				<div class="red_message"><?php echo $message; ?></div>
			<?php
			if(!empty($data)){
				foreach($data as $d){
			?>
				<div class="list-box">
					<div class="list-head clearfix">
						<span class="mbox">ID <?php echo $d['data_id']; ?></span>
						<span class="mbox"><a href="?page=diagnosis-generator-write.php&write_id=<?php echo $d['data_id']; ?>">編集する</a></span>
						<span onclick="view_div('short-code-area<?php echo $d['data_id']; ?>', '1')" class="mbox pointer">ショートコード取得</span>
					</div>
					<div id="form<?php echo $d['data_id']; ?>" class="listBox mbox clearfix">
						<div id="short-code-area<?php echo $d['data_id']; ?>" class="scode-area" style="display:none;">
							<textarea readonly>[OSDGSIS-FORM id=<?php echo $d['data_id']; ?>]</textarea>
							<span onclick="view_div('short-code-area<?php echo $d['data_id']; ?>', '0')" class="pointer-l">閉じる</span>
						</div>
						<div class="bar"><span class="ht">タイトル</span><span class="data"><?php echo self::h($d['form_title']); ?></span></div>
						<div class="bar"><span class="ht">診断方法</span><span class="data"><?php if($d['diagnosis_type']==1){ ?>設問形式<?php }else{ ?>システム<?php } ?></span></div>
						<div class="bar"><span class="ht">作成日時</span><span class="data"><?php echo self::h($d['create_time']); ?></span></div>
						<div class="bar"><span class="ht">更新日時</span><span class="data"><?php echo self::h($d['update_time']); ?></span></div>
					</div>
				</div>
			<?php
				}
			}
			?>

			</div>
		</div>
	</div>
<?php
}
?>