<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'Admin');
?>
<script type="text/javascript" src="<?php echo JS_PATH ?>task.js"></script>
<div class="pad-lr-10">
<div id="searchid">
<form name="searchform" action="<?php echo __APP__; ?>" method="get">
<input type="hidden" value="<?php echo MODULE_NAME ?>" name="m">
<input type="hidden" value="<?php echo CONTROLLER_NAME ?>" name="c">
<input type="hidden" value="<?php echo ACTION_NAME ?>" name="a">
<input type="hidden" value="<?php echo $_GET['mod']; ?>" name="mod">

<table width="100%" cellspacing="0" class="search-form">
    <tbody>
    <tr>
    	<td>
    		<div class="explain-col">	
    		 
				
				活动时间：
				<?php echo $form::datepicker('search[start_time]',$search['start_time'], 'start_time', 0);?>-&nbsp;
				<?php echo $form::datepicker('search[end_time]',$search['end_time'], 'end_time', 0);?>
				
				
				<input type="submit" name="searchbtn" class="button" value="<?php echo L('search');?>" />
			</div>
		</td>
		</tr>
    </tbody>
</table>
</form>
</div>
<form name="myform" id="myform" action="" method="post" >
<div class="table-list">
    <table width="100%">
		<thead>
		<tr>
            <!-- <th width="37">排序</th> -->
            <th width="40">ID</th>
			<th  width="100">今日试用申请数</th>
			<th width="100">手机申请数</th>
			<th width="100">闪电佣金申请数</th>
			<th width="100">购物返利抢购数量</th>
			<th width="100">返利手机抢购数量</th>
           
            <th width="118">统计时间</th>
			<th width="72">管理操作</th>
		</tr>
        </thead>
		<tbody>

		<?php foreach($lists as $r) : ; ?>
        <tr>
			
			<td align='center' ><?php echo $r['id'];?></td>
			<td align='center' ><?php echo $r['day_trial_Apply'];?></td>

			
			<td align='center'><?php echo $r['day_trial_Apply_phone'] ?></td>
			<td align='center'><?php echo $r['day_commission_Apply_phone'];?></td>
			<td align='center'><?php echo $r['day_rebate_Qualifications'] ?></td>
			<td align='center'><?php echo $r['day_rebate_phone_Qualifications'] ?></td>
			
			<td align='center'><?php echo dgmdate($r['time'], 'Y-m-d')?></td>
			<td align='center'>
				--
			</td>
		</tr>
		</tbody>
		<?php endforeach ?>
	</table>
    <div class="btn">
		<input type="button" class="button" value="批量删除" onclick="myform.action='<?php echo U('delete', array('dosubmit' => 1)) ?>';myform.submit();"/>
	</div>
	</div>
    <div id="pages"><?php echo $pages;?></div>
</div>
</form>
</div>
<script type="text/javascript">
function enter(id) {
  	window.top.art.dialog({
  		title:'提示', 
  		id:'edit',   
  		height:'80px',
  		content: '您确认已收到商家的付款？<br/><br/>本操作会将商品状态变更为待审核(已付款)并不可逆！',
  	}, 	function(){
  		location.href = "<?php echo __ROOT__ ?>/index.php?m=Task&c=Task&a=pay&task_id="+id;
  		window.top.art.dialog({id:'edit'}).close()
  		return false;
  	}, function(){
  		window.top.art.dialog({id:'edit'}).close()
  	});
}

function edit(obj, name) {
	window.top.art.dialog({id:'edit'}).close();
	window.top.art.dialog({title:'<?php echo L('edit')?> '+name+' ',id:'edit',iframe:obj.href,width:'700',height:'450'}, function(){var d = window.top.art.dialog({id:'edit'}).data.iframe;var form = d.document.getElementById('dosubmit');form.click();return false;}, function(){window.top.art.dialog({id:'edit'}).close()});
}
function checkuid() {
	var ids='';
	$("input[name='linkid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog({content:"<?php echo L('before_select_operations')?>",lock:true,width:'200',height:'50',time:1.5},function(){});
		return false;
	} else {
		myform.submit();
	}
}
//向下移动
function listorder_up(id) {
	$.get('?m=link&c=link&a=listorder_up&linkid='+id,null,function (msg) { 
	if (msg==1) { 
	//$("div [id=\'option"+id+"\']").remove(); 
		alert('<?php echo L('move_success')?>');
	} else {
	alert(msg); 
	} 
	}); 
} 
</script>
</body>
</html>