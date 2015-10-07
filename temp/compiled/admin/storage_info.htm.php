<!-- $Id: user_info.htm 16854 2009-12-07 06:20:09Z sxc_shop $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'validator.js,../js/transport.js,../js/region.js')); ?>
<div class="main-div">
<form action="storage.php" method="post" name="theForm" onsubmit="return validate();">
    <table cellspacing="1" cellpadding="3" width="100%">
      <tbody><tr>
        <td class="label">仓库名称:</td>
        <td><input type="text" name="store_name" value="<?php echo $this->_var['store']['store_name']; ?>" size="40">
        <span class="require-field">*</span></td>
      </tr>

      <tr>
        <td class="label"> 仓库描述:</td>
        <td>
          <textarea name="store_desc" rows="5" cols="40"><?php echo $this->_var['store']['store_desc']; ?></textarea>
        </td>
      </tr>
	  <tr>
        <td class="label">仓库状态:</td>
        <td>  开启<input <?php if ($this->_var['store']['store_status']): ?> <?php if ($this->_var['store']['store_status'] == 1): ?>checked="checked"<?php endif; ?><?php else: ?>checked="checked"<?php endif; ?> type="radio" name="store_status" value="1">关闭<input <?php if ($this->_var['store']['store_status'] == 0): ?>checked="checked"<?php endif; ?> type="radio" name="store_status" value="0"></td>
      </tr>
	   
	   <tr>
        <td class="label">所辖省份:</td>
        <td>
				<fieldset style="border:1px solid #DDEEF2">
  <legend style="background:#FFF"><?php echo $this->_var['lang']['shipping_area_regions']; ?>:</legend>
  <table style="width:600px" align="center">
  <tr>
    <td id="regionCell">
      <?php $_from = $this->_var['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('id', 'region');if (count($_from)):
    foreach ($_from AS $this->_var['id'] => $this->_var['region']):
?>
      <input type="checkbox" name="regions[]" value="<?php echo $this->_var['id']; ?>" checked="true" /> <?php echo $this->_var['region']; ?>&nbsp;&nbsp;
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </td>
  </tr>
  <tr>
    <td>
        <!-- <span  style="vertical-align: top"><?php echo $this->_var['lang']['label_country']; ?> </span>
        <select name="country" id="selCountries" onchange="region.changed(this, 1, 'selProvinces')" size="10" style="width:80px">
          <?php $_from = $this->_var['countries']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'country');if (count($_from)):
    foreach ($_from AS $this->_var['country']):
?>
          <option value="<?php echo $this->_var['country']['region_id']; ?>"><?php echo htmlspecialchars($this->_var['country']['region_name']); ?></option>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </select>
        <span  style="vertical-align: top"><?php echo $this->_var['lang']['label_province']; ?> </span>
        <select name="province" id="selProvinces" onchange="region.changed(this, 2, 'selCities')" size="10" style="width:80px">
          <option value=''><?php echo $this->_var['lang']['select_please']; ?></option>
        </select>
        <span  style="vertical-align: top"><?php echo $this->_var['lang']['label_city']; ?> </span>
        <select name="city" id="selCities" onchange="region.changed(this, 3, 'selDistricts')" size="10" style="width:80px">
          <option value=''><?php echo $this->_var['lang']['select_please']; ?></option>
        </select> -->
        <span  style="vertical-align: top"><?php echo $this->_var['lang']['label_district']; ?></span>
        <select name="district" id="selDistricts" size="10" style="width:130px">
          <option value=''><?php echo $this->_var['lang']['select_please']; ?></option>
        </select>
        <span  style="vertical-align: top"><input type="button" value="+" class="button" onclick="addRegion()" /></span>
    </td>
  </tr>
  </table >
</fieldset>	</td>
      </tr>

      <tr align="center">
        <td colspan="2">
          <input type="hidden" name="id" value="<?php echo $this->_var['sid']; ?>">
          <input type="submit" value=" 确定 " class="button">
          <input type="reset" value=" 重置 " class="button">
          <input type="hidden" name="act" value="<?php echo $this->_var['action']; ?>">
        </td>
      </tr>
    </tbody></table>
  </form>
</div>


<script language="JavaScript">


region.isAdmin = true;
region.district(88, 4, 'selDistricts');
if (document.forms['theForm'].elements['act'].value == "insert")
{
  document.forms['theForm'].elements['username'].focus();
}
else
{
  document.forms['theForm'].elements['email'].focus();
}

onload = function()
{
    // 开始检查订单
    startCheckOrder();
	
	
}

/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.isEmail("email", invalid_email, true);

    if (document.forms['theForm'].elements['act'].value == "insert")
    {
        validator.required("username",  no_username);
        validator.required("password", no_password);
        validator.required("confirm_password", no_confirm_password);
        validator.eqaul("password", "confirm_password", password_not_same);

        var password_value = document.forms['theForm'].elements['password'].value;
        if (password_value.length < 6)
        {
          validator.addErrorMsg(less_password);
        }
        if (/ /.test(password_value) == true)
        {
          validator.addErrorMsg(passwd_balnk);
        }
    }
    else if (document.forms['theForm'].elements['act'].value == "update")
    {
        var newpass = document.forms['theForm'].elements['password'];
        var confirm_password = document.forms['theForm'].elements['confirm_password'];
        if(newpass.value.length > 0 || confirm_password.value.length)
        {
          if(newpass.value.length >= 6 || confirm_password.value.length >= 6)
          {
            validator.eqaul("password", "confirm_password", password_not_same);
          }
          else
          {
            validator.addErrorMsg(password_len_err);
          }
        }
    }

    return validator.passed();
}


/**
 * 添加一个区域
 */
function addRegion()
{
    var selCountry  = document.forms['theForm'].elements['country'];
    var selProvince = document.forms['theForm'].elements['province'];
    var selCity     = document.forms['theForm'].elements['city'];
    var selDistrict = document.forms['theForm'].elements['district'];
    var regionCell  = document.getElementById("regionCell");

    if (selDistrict.selectedIndex > 0)
    {
        regionId = selDistrict.options[selDistrict.selectedIndex].value;
        regionName = selDistrict.options[selDistrict.selectedIndex].text;
    }
    else
    {
        if (selCity.selectedIndex > 0)
        {
            regionId = selCity.options[selCity.selectedIndex].value;
            regionName = selCity.options[selCity.selectedIndex].text;
        }
        else
        {
            if (selProvince.selectedIndex > 0)
            {
                regionId = selProvince.options[selProvince.selectedIndex].value;
                regionName = selProvince.options[selProvince.selectedIndex].text;
            }
            else
            {
                if (selCountry.selectedIndex >= 0)
                {
                    regionId = selCountry.options[selCountry.selectedIndex].value;
                    regionName = selCountry.options[selCountry.selectedIndex].text;
                }
                else
                {
                    return;
                }
            }
        }
    }

    // 检查该地区是否已经存在
    exists = false;
    for (i = 0; i < document.forms['theForm'].elements.length; i++)
    {
      if (document.forms['theForm'].elements[i].type=="checkbox")
      {
        if (document.forms['theForm'].elements[i].value == regionId)
        {
          exists = true;
          alert(region_exists);
        }
      }
    }
    // 创建checkbox
    if (!exists)
    {
      regionCell.innerHTML += "<input type='checkbox' name='regions[]' value='" + regionId + "' checked='true' /> " + regionName + "&nbsp;&nbsp;";
    }
}


</script>

<?php echo $this->fetch('pagefooter.htm'); ?>
