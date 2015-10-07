<!-- $Id: article_info.htm 16780 2009-11-09 09:28:30Z sxc_shop $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,selectzone.js,validator.js,jquery-1.4.2.js')); ?>
<!-- start goods form -->
<div class="tab-div">
  <div id="tabbar-div">
    <p>
	  
    </p>
  </div>

  <div id="tabbody-div">
  	
  	<form method="post" action="lottery.php" id="prize-table"  >
     <div class="list-div" id="listDiv">

<table cellspacing='1' cellpadding='3' id='list-table'>
  <tr>
    <th>序号</th>
    <th>活动名称</th>
 <!--      <th>关键词</th> -->
    <th>开启状态</th>
     <th>中奖人数</th>
    <th>创建时间</th>
    <th>操作</th>
  </tr>
   <?php $_from = $this->_var['lottery']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
    <tr id="<?php echo $this->_var['list']['id']; ?>">
    <td class="first-cell">
    	<?php echo $this->_var['list']['id']; ?>
    </td>
    <td >
    <span ><?php echo $this->_var['list']['title']; ?> </span></td>
 <!--   <td align="center"><span><?php echo $this->_var['list']['keyword']; ?></span></td> -->
    <td align="left"><span><?php if ($this->_var['list']['status'] == 1): ?>开启<?php else: ?>关闭<?php endif; ?></span></td>
    <td align="center"><?php echo $this->_var['list']['lucknums']; ?></td>
    <td align="center"><span><?php echo $this->_var['list']['createtime']; ?></span></td>
    
   
    <td align="center" nowrap="true"><span>
     <a href="lottery.php?act=lottery_record&lid=<?php echo $this->_var['list']['id']; ?>" title="中奖记录">中奖记录</a>&nbsp;
      <a href="lottery.php?act=edit_lottery&id=<?php echo $this->_var['list']['id']; ?>" title="编辑">编辑</a>&nbsp;
      <a href="/lottery2.php?act=index&id=<?php echo $this->_var['list']['id']; ?>" title="编辑" target="_blank">试玩</a>&nbsp;
     <!--  --><a href="javascript:del_lottery(<?php echo $this->_var['list']['id']; ?>)" id="del_lottery"  title="移除">移除</a><!--  --></span>
    </td>
   </tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    
</table>


</div>


 
    </form>
 <script>
 	
	function del_lottery(index)
	{
			
		if(confirm("确定要删除这条记录??"))
		{
			$.post("lottery.php?act=del_lottery",{"id":index},function(data){
			
			if(data=="success")
			{
				alert("删除成功");
				$("#"+index).css("display","none");
			}
			else
			{
				alert("删除失败");	
			}
		},"text");
		
		}
				
	}
 </script>
   
 
<?php echo $this->fetch('pagefooter.htm'); ?>