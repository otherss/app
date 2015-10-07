<!-- $Id: category_info.htm 16752 2009-10-20 09:59:38Z wangleisvn $ -->
<?php echo $this->fetch('pageheader2.htm'); ?>


	<link rel="stylesheet" type="text/css" href="styles/datetimepicker.css" />
	<link rel="stylesheet" type="text/css" href="styles/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="styles/jquery-ui-timepicker-addon.min.css" />
	
<!-- start add new category form -->
<div class="main-div">
  <form action="http://newpush.appcan.cn/oauth/send" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
  <table width="100%" id="general-table">
      <tr>
        <td class="label">推送题目:</td>
        <td>
          <input type='text' name='title' maxlength="20" value='123' size='27' /> <font color="red">*</font>
        </td>
      </tr>
  	
      <tr>
        <td class="label">平台:</td>
        <td>
          <input type="radio" name="platforms" value="1" checked=checked /> 安卓
          <input type="radio" name="platforms" value="2" /> IOS
        </td>
      </tr>
	  
	  <tr>
        <td class="label">推送时间:</td>
        <td>
          <input type="radio" name="time" value="0" checked=checked   /> 立即
          <input type="radio" name="time" value="1" /> 定时
        </td>
      </tr>
	  
	 <tr>
        <td class="label">定时发送:</td>
        <td>
          <input type='text' name='pushTime' id="pushTime" maxlength="20" value='' size='27' /> 
        </td>
      </tr>
	  
	   <tr>
        <td class="label">缓存时间:</td>
        <td>
          <input type='text' name='keepHours' maxlength="20" value='2' size='27' /> 
        </td>
      </tr>
	  
	  <tr>
        <td class="label">推送内容:</td>
        <td>
          <input type='text' name='body' maxlength="20"  size='50'  value="{'msgName':'推送消息标题','news':1}" /> 
        </td>
      </tr>
	  
	  <tr>
        <td class="label">参数键值:</td>
        <td>
          <input type='text' name='keys' maxlength="20" value='' size='27'  value="" /> 
        </td>
      </tr>
	  
	  <tr>
        <td class="label">参数值:</td>
        <td>
          <input type='text' name='values' maxlength="20" value='' size='27'  value="" /> 
        </td>
      </tr>
    
      
      </table>
      <div class="button-div">
        <input type="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" />
        <input type="reset" value="<?php echo $this->_var['lang']['button_reset']; ?>" />
      </div>
    <input type="hidden" name="save" value="2" />
    <input type="hidden" name="userScope" value="1" />
	<input type="hidden" value="2cbe8c22-1b2b-a136-0f3b-447eec1f0017" name="authtoken">
		<input type="hidden" value="lit520@qq.com" name="email">
		<input type="hidden" value="11420244" name="appId">
    
  </form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js,jquery-1.10.2.min.js,jquery-ui-1.9.2-min.js,jquery.ui.datepicker-zh-CN.min.js,jquery-ui-timepicker-addon.min.js')); ?>

<script language="JavaScript">

document.forms['theForm'].elements['title'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
	
  validator = new Validator("theForm");
  validator.required("title",  "title不能为空");
 
  return validator.passed();
  
}

</script>

<script>

		
		$(document).ready(function() { 
			timeFormatObj = {
				showSecond: true,  
				changeMonth: true,   
				timeFormat: 'HH:mm:ss',  
				dateFormat: 'yy-mm-dd',
				
				stepHour: 1,
				stepMinute: 5,
				stepSecond: 5
			};
     	   $('#pushTime').datetimepicker(timeFormatObj);
		   
         });
</script>


<?php echo $this->fetch('pagefooter.htm'); ?>