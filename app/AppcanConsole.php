<!DOCTYPE html>
<html>
<head>
	<title>信德国际APP后台</title>
<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="phpcss/jquery.fileupload-ui.css">
<link rel="stylesheet" type="text/css" href="phpcss/xdgj.css">
<script type="text/javascript" src="jquery/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="jqueryfileupload/jquery.ui.widget.js"></script>
<script type="text/javascript" src="jqueryfileupload/jqueryfileupload.min.js"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="phpjs/eweditor.js"></script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="generator" content="PHPMaker v10.0.3">
<style>
.read_text{width:397px;height:100px}.kuanc3{width:58px}.shuo_in{width:310px;height:25px}.chicun{color:#000000}.xuanze_r{background:#d4ebff;border:1px solid #8ac7ff;cursor:pointer}.youxi{font-size:14px;line-height:30px;border-bottom:1px solid #d0d0d0}.radio_con li{float:left;margin-right:15px;margin-bottom:10px;width:100px}.dbsb{background:url(../images/dbsb.png);width:62px;height:63px;background-repeat:no-repeat}.padd_l{padding-left:60px}.tupian_con{}.tupian{background:url(../images/tupian.png);width:190px;height:320px}.tuisong{width:150px}.tuisong li{font-size:14px;line-height:40px;height:40px;cursor:pointer}.now_tui{background:url(../images/now_tui.png);background-position:left;background-repeat:no-repeat;padding-left:18px}.ch_tui{background:url(../images/ch_tui.png);background-position:left;background-repeat:no-repeat;padding-left:18px}.tui_con{font-size:12px;line-height:30px}.ptop{padding-top:5px}.tui_left{width:100px}.save_input{width:60px;height:25px}.iphone_platform{border:1px solid #d0d0d0;font-size:12px;line-height:30px;padding:0 10px}.andriod_platform{border:1px solid #ff7701;font-size:12px;line-height:30px;padding:0 10px}.user_checked{background:url(../images/user_checked.png);width:470px;height:420px;padding:10px;background-size:100% 100%;background-repeat:no-repeat}
</style>
<script>
var userItem = '<tr class="boder_b"><td width="10%"><span class="radioButton" onclick="radioToken(\'{token}\')"><input type="radio" name="softToken" value="{token}"></span></td><td width="20%">{uid}</td><td width="20%">{name}</td><td width="50%">{token}</td></tr>';

$.onload = function(){
	alert(112332);
}
window.onload = function(){
	flushUserList();
}
function pushMsg(){
	var type = 0;
	if($("#pushDrvice:checked").val() == "GetAndroidUserList"){
		type = 1;//安卓平台是1
	}
	$.post("AppcanPush.php?op="+$("#ty:checked").val(),{"title":$("#title").val(),"content":$("#content").val(),"token":$("#softToken:checked").val(),"type":type},function(json){
		console.log(json);
		if(json.status == "ok"){
			alert("消息推送成功！");
		}else{
			alert(json.messageList? json.messageList : "推送失败，可能用户不存在，请检查页面参数。");
		}
		
	},"json");
}
function flushUserList(){
	$("#userList").html("");
	$("#pages").html("");
	
	$.get("AppcanPush.php?op=" + $("#pushDrvice:checked").val() ,function(json){
		console.log(json);
		if(json.userList){
			var strUserList = "";
			for(i=0;i<json.userList.length;i++){
				strUserList += userItem.replaceAll("{name}",json.userList[i].userNick).replaceAll("{uid}",json.userList[i].userId).replaceAll("{token}",json.userList[i].softToken);
			}
			$("#userList").html(strUserList);
			$("#pages").html("<strong>共"+json.userList.length+"条</strong>");
		}else{
			$("#pages").html("<strong>没有用户在线</strong>");
		}
	},"json");
	
}
String.prototype.replaceAll = function(reallyDo, replaceWith, ignoreCase) { 
　 if (!RegExp.prototype.isPrototypeOf(reallyDo)) { 
return this.replace(new RegExp(reallyDo, (ignoreCase ? "gi": "g")), replaceWith); 
} else { 
return this.replace(reallyDo, replaceWith); 
} 
} 
function radioType(tt){
	if(tt=="pushSingel"){
		$("#ulistTR").css('display',''); 
	}else{
		$("#ulistTR").css('display','none'); 
	}
}

</script>
</head>
<body>
<div class="ewLayout">
	<!-- content (begin) -->
	<table id="ewContentTable" class="ewContentTable">
		<tr>
		<td id="ewContentColumn" class="ewContentColumn">
			<!-- right column (begin) -->
				<p class="ewSiteTitle">create by Roy QQ:381281 有需要制作APP与插件的，可以与我QQ联系。 嗯，我只是工作之余挣点外快~</p>

<table class="ewStdTable"><tr><td><ul class="breadcrumb"><li><a href="index.php">Home</a><span class="divider">/</span></li><li><a href="appcan.php"><span id="ewPageCaption">APP消息推送</span></a><span class="divider">/</span></li><li class="active">推送</li></ul></td></tr></table><table class="ewStdTable"><tr><td><div class="ewMessageDialog"></div></td></tr></table>
<form name="fview_app_downadd" id="fview_app_downadd" class="ewForm form-horizontal" action="appcan.php" method="post">
<table class="ewGrid" style="width:691px;"><tr><td>
<table id="tbl_view_app_downadd" class="table table-bordered table-striped">
	<tr id="r_name">
		<td width="100"><span id="elh_view_app_down_name">标题<span class="ewRequired">&nbsp;*</span></span></td>
		<td>
<span id="el_view_app_down_name" class="control-group">
<input type="text" data-field="x_name" name="title" id="title" size="30" maxlength="50" placeholder="推送标题。" value="">
</span>
</td>
	</tr>
	<tr id="r_code">
		<td><span id="elh_view_app_down_code">内容<span class="ewRequired">&nbsp;*</span></span></td>
		<td>
<span id="el_view_app_down_code" class="control-group">
<input type="text" data-field="x_code" name="content" style="width:90%;" id="content" size="30" maxlength="60" placeholder="推送内容。" value="">
</span>
</td>
	</tr>
	<tr>
	  <td>目标设备</td>
	  <td>
	    <label><input type="radio" name="pushDrvice" id="pushDrvice" value="GetAndroidUserList" checked="checked" >Android</label>
	    <label><input type="radio" name="pushDrvice" id="pushDrvice" value="GetIosUserList" >Apple</label></td>
	  </tr>
	<tr>
	  <td>推送目标</td>
	  <td>
<label onclick="radioType('pushAll')" ><input type="radio" name="ty" id="ty" value="pushAll"  checked="checked">群推</label>
<label onclick="radioType('pushSingel');"><input type="radio" name="ty" id="ty" value="pushSingel">单推</label></td>
	  </tr>
	<tr id="ulistTR" style="display:none;">
	  <td>在线用户数量<br>
	    <br>
	    <br>	    <button class="btn btn-primary ewButton" onclick="flushUserList();return false" name="btnAction" id="btnFlush">刷新</button></td>
	  <td><div class="user_checked ul_margin_top">
                                                            <div class="table_header ul_font_weight">
                                                                <table width="100%" border="0">
                                                                  <tbody><tr>
                                                                    <td width="30%" align="center">用户ID</td>
                                                                    <td width="20%" align="center">昵称</td>
                                                                    <td width="50%" align="center">sofToken</td>
                                                                  </tr>
                                                                </tbody></table>
                                                            </div>
                                                            <div id="jp-container" class="jp-container ul_margin_top">
<table id="userList" width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" frame="hsides" rules="rows">
</table>
                                                             </div>
                                                             <div class="page1" id="pages"><li class="page_kuang"><strong>共9条</strong> </li></div>
                                                             <div class="clr"></div>
                                                        </div></td>
	  </tr>
</table>
</td></tr></table>
<button class="btn btn-primary ewButton" onclick="pushMsg();return false" name="btnAction" id="btnAction">推送</button>
</form>
			<!-- right column (end) -->
					</td></tr>
	</table>
    
    
    
	<!-- content (end) -->
</div>
<!-- message box -->
<div id="ewMsgBox" class="modal hide" data-backdrop="false"><div class="modal-body"></div><div class="modal-footer"><a href="#" class="btn btn-primary ewButton" data-dismiss="modal" aria-hidden="true">OK</a></div></div>
<!-- tooltip -->
<div id="ewTooltip"></div>
</body>
</html>
