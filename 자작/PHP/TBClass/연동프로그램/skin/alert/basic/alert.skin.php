<style type="text/css">
div.alert-message {
	display: block;
	padding: 13px 12px 12px;
	font-weight: bold;
	font-size: 14px;
	color: white;
	background-color: #2ba6cb;
	border: 1px solid rgba(0, 0, 0, 0.1);
	margin-bottom: 12px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	-ms-border-radius: 3px;
	-o-border-radius: 3px;
	border-radius: 3px;
	text-shadow: 0 -1px rgba(0, 0, 0, 0.3);
	/*position: relative;*/
	position:absolute; left:20%; top:73px; width:700px; z-index:99;
}
div.alert-message .box-icon {
	display: block;
	float: left;
	background-image: url('<?php echo $skin_url ?>/img/icon.png');
	width: 30px;
	height: 25px;
	margin-top: -2px;
	background-position: -8px -8px;
}
div.alert-message p {
	margin: 0px;
}
div.alert-message.success {
	background-color: #5da423;
	color: #fff;
	text-shadow: 0 -1px rgba(0, 0, 0, 0.3);
}
div.alert-message.success .box-icon {
	background-position: -48px -8px;
}
div.alert-message.warning {
	background-color: #e3b000;
	color: #fff;
	text-shadow: 0 -1px rgba(0, 0, 0, 0.3);
}
div.alert-message.warning .box-icon {
	background-position: -88px -8px;
}
div.alert-message.error {
	background-color: #c60f13;
	color: #fff;
	text-shadow: 0 -1px rgba(0, 0, 0, 0.3);
}
div.alert-message.error .box-icon {
	background-position: -128px -8px;
}
div.alert-message a.close {
	color: #fff;
	position: absolute;
	right: 4px;
	top: -1px;
	font-size: 17px;
	opacity: 0.2;
	padding: 4px;
}
div.alert-message a.close:hover, div.alert-box a.close:focus {
	opacity: 0.4;
}
</style>
<script type="text/javascript"> 
$(function(){   
	// 닫기 버튼 실행시
	$(".alert-message").delegate("a.close", "click", function(event) {
		event.preventDefault();
		$(this).closest(".alert-message").fadeOut(function(event){
			$(this).remove();
		});
	});

	// 1.3초후 alert 삭제
	$('.alert-message').delay(1300).fadeOut(300, function(){
		$(this).remove();
	});
});
</script>

<div class="alert-message <?php echo $type ?>">
	<div class="box-icon"></div>
	<p><?php echo $msg ?><a href="" class="close">&times;</a>
</div>


<?/*
<div class="alert-message info">
	<div class="box-icon"></div>
	<p>This is an info box<a href="" class="close">&times;</a>
</div>
<div class="alert-message success">
	<div class="box-icon"></div>
	<p>This is a success box<a href="" class="close">&times;</a>
</div>
<div class="alert-message warning">
	<div class="box-icon"></div>
	<p>This is a warning box<a href="" class="close">&times;</a>
</div>
<div class="alert-message error">
	<div class="box-icon"></div>
	<p>This is an alert box<a href="" class="close">&times;</a>
</div>
*/?>