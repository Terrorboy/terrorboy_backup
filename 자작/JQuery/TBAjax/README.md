TBAjax ����
---
var SearchSubAdmin = new AjaxTB(��� Ÿ��, ����� ������ Ǯurl, ���ǰ�? ����);
SearchSubAdmin.Sender();


TBAjax ���ۿ���
---
```
<script type="text/javascript">
$(function(){
	var SearchSubAdmin = new AjaxTB("#test", "./test.php", "?test=hi");
	SearchSubAdmin.Sender();
});
</script>
<div id="test"></div>
```







TBAjaxForm ����
---
<form action="�����ġ" method="��ҵ�" id="TBAjaxForm" target="����� ��� ��ü">


TBAjaxForm ���ۿ���
---
```
<form action="test2.php" method="GET" id="TBAjaxForm" target="#LoadView">
	<input type="text" name="text" value="text">
	<select name="selectbox">
		<option value="selectbox" selected>selectbox</option>
	</select>
	<input type="radio" name="radio" value="radio" checked>
	<input type="checkbox" name="checkbox" value="checkbox" checked>
	<input type="submit" value="����">
</form>
<div id="LoadView"></div>
```