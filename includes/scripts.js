window.setInterval(function () {
		$.get(window.location.href,"",function(data) {
			const html = $(data);
			const newContent = html.find('#fsrscreen_nextDepartureBar').html();
			console.log($('#fsrscreen_nextDepartureBar'));
			$('#fsrscreen_nextDepartureBar').html(newContent);
		});
	},
	30000
)