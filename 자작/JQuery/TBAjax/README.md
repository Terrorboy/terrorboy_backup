TBAjax 사용법
---
var SearchSubAdmin = new AjaxTB(출력 타겟, 통신한 페이지 풀url, 조건값? 포함);
SearchSubAdmin.Sender();


TBAjax 동작예제
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







TBAjaxForm 사용법
---
<form action="통신위치" method="멧소드" id="TBAjaxForm" target="출력할 뷰어 객체">


TBAjaxForm 동작예제
---
```
<form action="test2.php" method="GET" id="TBAjaxForm" target="#LoadView">
	<input type="text" name="text" value="text">
	<select name="selectbox">
		<option value="selectbox" selected>selectbox</option>
	</select>
	<input type="radio" name="radio" value="radio" checked>
	<input type="checkbox" name="checkbox" value="checkbox" checked>
	<input type="submit" value="전송">
</form>
<div id="LoadView"></div>
```