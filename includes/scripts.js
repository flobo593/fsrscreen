window.setInterval(function () {
		let div = document.getElementById('fsrscreen_nextDepartureBar');
		$('#fsrscreen_nextDepartureBar').load(window.location.href + "#fsrscreen_nextDepartureBar");
	},
	500
)