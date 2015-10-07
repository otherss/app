<!-- $Id: goods_list.htm 17126 2010-04-23 10:30:26Z liuhui $ -->

<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>

<!-- 商品搜索 -->
<div class="form-div">
  <form action="javascript:searchGoods()" name="searchForm">
    <img src="images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
   <select name="cat_id"><option value="-1">请选择分类</option><?php echo $this->_var['cat_list']; ?></select>
    <!-- 分类 -->
    <select name="goods_id" id="goodsDiv"><option value="-1">请选择商品</option><?php echo $this->_var['storage_goods_list']; ?></select>
    <!-- 品牌 -->
    <select name="storage_id"><option value="-1">请选择仓库</option><?php echo $this->html_options(array('selected'=>$this->_var['storage_id'],'options'=>$this->_var['storage_list'])); ?></select>
   
    <!-- 关键字 -->
    <?php echo $this->_var['lang']['keyword']; ?> <input type="text" name="keyword" size="15" />
    <input type="submit" value="<?php echo $this->_var['lang']['button_search']; ?>" class="button" />
  </form>
</div>


<script language="JavaScript">
    function searchGoods()
    {

     
        listTable.filter['cat_id'] = document.forms['searchForm'].elements['cat_id'].value;
        listTable.filter['goods_id'] = document.forms['searchForm'].elements['goods_id'].value;
	
        listTable.filter['storage_id'] = document.forms['searchForm'].elements['storage_id'].value;
        
     
      

        listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
        listTable.filter['page'] = 1;
		
			
	listTable.listCallback = function(result, txt)
	{
	  if (result.error > 0)
	  {
	    alert(result.message);
	  }
	  else
	  {
	    try
	    {
	      document.getElementById('listDiv').innerHTML = result.content;
	    
	      if(result.select!=null)
	    	  {
	    	  document.getElementById('goodsDiv').innerHTML = result.select;
	    	  }
	      
	      if (typeof result.filter == "object")
	      {
	        listTable.filter = result.filter;
	      }
	
	      listTable.pageCount = result.page_count;
	    }
	    catch (e)
	    {
	      alert(e.message);
	    }
	  }
	}

        listTable.loadList();
    }
</script>

<!-- 商品列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start goods list -->
  <div class="list-div" id="listDiv">
<?php endif; ?>
<table cellpadding="3" cellspacing="1">
  <tr>
    <th>
     
     <!--  <a href="javascript:listTable.sort('goods_id'); "><?php echo $this->_var['lang']['record_id']; ?></a> -->序号
    </th>
	 <th>仓库</th>
    <th>商品名称</th>
   <th>库存</th>
    <th>入库</th>
  <th>出库</th>
 
  <tr>
  <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
  <tr>
    <td align="center"><?php echo $this->_var['goods']['id']; ?></td>
	<td align="center"><?php echo $this->_var['goods']['store_name']; ?></td>
    <td class="first-cell" style="<?php if ($this->_var['goods']['is_promote']): ?>color:red;<?php endif; ?>"><span><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></span></td>
   <td align="center"><span id="number<?php echo $this->_var['goods']['id']; ?>"><?php echo $this->_var['goods']['number']; ?></span></td>
    <td align="center">+<span onclick="listTable.edit(this, 'edit_add_number', <?php echo $this->_var['goods']['id']; ?>)">0</span></td>
  <td align="center">-<span onclick="listTable.edit(this, 'edit_cut_number', <?php echo $this->_var['goods']['id']; ?>)">0</span></td>
   
  </tr>
  <?php endforeach; else: ?>
  <tr><td class="no-records" colspan="10"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<!-- end goods list -->

<!-- 分页 -->
<table id="page-table" cellspacing="0">
  <tr>
    <td align="right" nowrap="true">
    <?php echo $this->fetch('page.htm'); ?>
    </td>
  </tr>
</table>

<?php if ($this->_var['full_page']): ?>
</div>

<div>

</div>
</form>

<script type="text/javascript">
  listTable.recordCount = <?php echo $this->_var['record_count']; ?>;
  listTable.pageCount = <?php echo $this->_var['page_count']; ?>;

  <?php $_from = $this->_var['filter']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
  listTable.filter.<?php echo $this->_var['key']; ?> = '<?php echo $this->_var['item']; ?>';
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

  
  onload = function()
  {
    startCheckOrder(); // 开始检查订单
    document.forms['listForm'].reset();
  }


</script>
<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>