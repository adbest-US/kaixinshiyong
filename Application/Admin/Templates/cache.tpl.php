<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header');?>
<style type="text/css">
.sbs{}
.sbul{margin:10px;}
.sbul li{line-height:30px;}
.button{margin-top:20px;}
.subnav,.ifm{display:none;}
</style>
<div class="pad-10">
<form action="<?php echo U('clear', array('fromhash' => FROMHASH)) ?>" target="cache_if" method="post" id="myform" name="myform">
<input type="hidden" name="dosubmit" value="1">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<div class="col-2">
<h6>提示信息</h6>
<div class="sbs" id="update_tips" style="height:360px; overflow:auto;">
	<ul id="file" class="sbul">
	</ul>
</div>
</div>
</form>
<iframe id="cache_if" name="cache_if" class="ifm"></iframe>
<iframe id="hidden" name="hidden"  width="0" height="0" frameborder=0></iframe>
</div>
<script type="text/javascript">
<?php if ($isdosubmit == 1): ?>
document.myform.submit();
<?php endif; ?>
function addtext(data) {
	$('#file').append(data);
	document.getElementById('update_tips').scrollTop = document.getElementById('update_tips').scrollHeight;
}
</script>
</body>
</html>