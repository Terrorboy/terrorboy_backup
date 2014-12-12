$(function() {

	// 기본객체 생성
	var gui = require('nw.gui');
	var win = gui.Window.get();
	var sysPath = require('path');
	var GetMac = require('getmac');
	var Mac = [];

	// 개발자툴
	win.showDevTools();

	// 실행 경로
	window.sysPath = sysPath.dirname(process.execPath);

	// 맥어드레스 검출
	GetMac.getMac(function(err,macAddress){ Mac[0] = macAddress; alert(macAddress); });

	//window.Mac = Mac;
	console.log(Mac);
	


	/* ==================================================== */
	/* = 트레이 설정                                      = */
	/* ---------------------------------------------------- */
	// Create a tray icon
	var tray = new gui.Tray({ title: 'Tray', icon: './img/terrorboy.png' });

	// 메뉴 선언
	var menu = new gui.Menu();

	// 메뉴 아이템
	menu.append(new gui.MenuItem({
				type: "normal",
				label: '종료',
				click: function() {

					// 트레이 제거
					tray.remove();
					tray = null;

					// 윈도우 제거
					if(win != null) {
						
						win.close(true);
						win = null;
					}
					this.close(true);

					gui.App.quit();
				}
		}));


	// 메뉴를 트레이로 띄움
	tray.menu = menu;
	/* ---------------------------------------------------- */
	/* = 트레이 설정                                      = */
	/* ==================================================== */



	/* ==================================================== */
	/* = 종료 설정                                        = */
	/* ---------------------------------------------------- */
	win.on('close', function() {

		this.hide();

		if(tray != null) {
			tray.remove();
			tray = null;
		}

		if (win != null) { 
			win.close(true);
			win = null;
		}

		this.close(true);
		gui.App.quit();
	});

	win.on('closed', function() {

		this.hide();
		if(tray != null) {
			
			tray.remove();
			tray = null;
		}

		if(win != null) {

			win.close(true);
			win = null;
		}

		this.close(true);
		gui.App.quit();
	});
	/* ---------------------------------------------------- */
	/* = 종료 설정                                        = */
	/* ==================================================== */
});
