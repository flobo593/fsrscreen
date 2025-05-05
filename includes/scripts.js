window.setInterval(function () {
		let div = document.getElementById('fsrscreen_nextDepartureBar');
		$.get(window.location.href, function(data) {
			const html = $(data);
			const newContent = html.find("#fsrscreen_nextDepartureBar").html();
			$('#fsrscreen_nextDepartureBar').html(newContent);
		});
	},
	30000
)