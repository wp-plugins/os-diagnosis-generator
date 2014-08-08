<?php
if(class_exists('DiagnosisAdmin')){
	// POST権限
	if(isset($osdg_option_data) && isset($osdg_option_data['post_authority'])){
		$post_authority = $osdg_option_data['post_authority'];
	}else{
		$post_authority = 1;
	}
	// タイムゾーン
	if(isset($osdg_option_data) && isset($osdg_option_data['timezone'])){
		$timezone = $osdg_option_data['timezone'];
	}else{
		$timezone = osdgTimezoneGet();
	}
	// タイムゾーン一覧取得
	$timeZoneData = TimezoneClass::zonelist();
	// ライセンス
	if(isset($osdg_option_data) && isset($osdg_option_data['license'])){
		$license = self::h($osdg_option_data['license']);
	}else{
		$license = "free";
	}
?>

<script>
function click_views(ids){
	j('#'+ids).css("display", "block");
}
function click_views_none(ids){
	j('#'+ids).css("display", "none");
}
</script>

	<div id="diagnosis-plugin">
		<div class="diagnosis-wrap">
			<h2>オプション</h2>
			<div class="diagnosis-contents">
				<p style="color:red;"><?php echo $message; ?></p>
				<form action="admin.php?page=diagnosis-generator-options.php" method="POST">
					<table>
						<tr>
							<th>診断ができるユーザ</th>
							<td>
								<select name="post_authority">
									<option value="1" <?php if($post_authority==1){ ?>selected<?php } ?>>全ユーザが利用できる</option>
									<option value="2" <?php if($post_authority==2){ ?>selected<?php } ?>>登録ユーザが利用できる</option>
									<option value="3" <?php if($post_authority==3){ ?>selected<?php } ?>>診断ごとに利用の制限をする</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>タイムゾーン</th>
							<td>
								<select name="timezone">
									<option value="" <?php if(empty($timezone)){ ?>selected<?php } ?>>設定しない</option>

								<?php
								foreach($timeZoneData as $group => $d){
								?>
									<optgroup label="<?php echo $group; ?>">

									<?php
									foreach($d as $zone => $name){
									?>
									<option value="<?php echo $zone; ?>" <?php if($timezone==$zone){ ?>selected<?php } ?>><?php echo $name; ?></option>
									<?php
									echo "\n";
									}
									?>

									</optgroup>
								<?php
								}
								?>

								</select>
							</td>
						</tr>
						<tr>
							<th>ライセンス</th>
							<td>
								<input type="text" name="license" value="<?php echo $license; ?>" /><br />
								<div style="font-size:11px;">※ライセンスを取得した方のみ、ご記入ください。デフォルトは「free」です。</div>
							</td>
						</tr>
					</table>
					<input type="hidden" name="option" value="1" />
					<div class="submit">
						<input type="submit" name="submit" value="更新する" />
					</div>
				</form>
				<br />
				<a href="#" onclick="click_views('user_setsumei')">初期化する</a>
				<div id="user_setsumei" style="display:none;color:red;">
					<p>初期化します。初期化すると全てのデータが削除されます。</p>
					<p>よろしいですか？</p>
					<form action="admin.php?page=diagnosis-generator-options.php" method="POST">
						<input type="hidden" name="format" value="1" />
						<div class="submit">
							<input type="submit" name="yes" value="はい" style="width:80px;" />　
							<input type="button" name="no" value="いいえ" onclick="click_views_none('user_setsumei')" style="width:80px;" />
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

<?php
}
?>