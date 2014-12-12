// 노티피케이션
function TBnotification(Title, Content, Icon) {

	// 입력값이 없을경우
	if(!Icon) Icon = './img/terrorboy.png';
	if(!Title) Title = '프로그램 구동중입니다.';
	if(!Content) Content = '프로그램을 구동하기 위하여 설정중입니다.';

	window.LOCAL_NW.desktopNotifications.notify(Icon, Title, Content, function(){
		
		$('#status').text('Clicked on '+Title);
		$('#status').fadeIn('fast',function(){
			setTimeout(function(){
				$('#status').fadeOut('fast');
			},1800);
		});
	});
}


// 현재 시간 표시
function TimerView(Target) {

	if(!$(Target)[0]) return;

	$(Target).text("00:00:00");

	setInterval(function () {

		var Times = new Date();
		var H = Times.getHours();
		var M = Times.getMinutes();
		var S = Times.getSeconds();
		var Timer = '';

		Timer += ((H <10)?  '0' : '' )+H;
		Timer += ((M <10)? ':0' : ':')+M;
		Timer += ((S <10)? ':0' : ':')+S;

		$(Target).text(Timer);

	}, 1000);
}


// 응용프로그램 실행
function ExERun(File) {

	var execFile = require ('child_process').execFile;
	var filePath = window.sysPath + File;
	var child;

	child = execFile(filePath, function(error,stdout,stderr) { 
		if (error) {
			console.log(error.stack); 
			console.log('Error code: '+ error.code); 
			console.log('Signal received: '+  error.signal);
		} 
		console.log('Child Process stdout: '+ stdout);
		console.log('Child Process stderr: '+ stderr);
	}); 

	child.on('exit', function (code) { 
		console.log('Child process exited '+'with exit code '+ code);
	});
}


// 맥어드레스 뷰어
function MacView(Target) {

	if(!$(Target)[0]) return;
	$(Target).text(window.macAddress);
}