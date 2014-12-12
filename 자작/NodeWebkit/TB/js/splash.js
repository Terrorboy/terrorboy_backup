// Load native UI library
var gui = require('nw.gui');

function open(){
	/*
	var win = gui.Window.open('index.html', {
		position: 'center',
		width: 800,
		height: 750,
		frame: false,
		toolbar: false,
		icon: "./img/terrorboy.png",
		fullscreen: true
	});
	*/
	var win = gui.Window.open('index.html', {
		position: 'center',
		width: 800,
		height: 750,
		frame: false,
		toolbar: false,
		icon: "./img/terrorboy.png",
		fullscreen: true
	});
	this.close(true);
}
setTimeout("open()",2500);