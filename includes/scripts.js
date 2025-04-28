function adjustFontSize (element) {
	let initialHeight = parseFloat(window.getComputedStyle(element, null).getPropertyValue('height'));
	let initialFontSize = parseFloat(window.getComputedStyle(element, null).getPropertyValue('font-size'));
	let targetHeight = parseFloat(window.getComputedStyle(element, null).getPropertyValue('font-size'));
	let calculatedFontSize = Math.round(2*(initialFontSize * targetHeight) / initialHeight)/2;
	element.style.fontSize = calculatedFontSize * 0.8 + "px";
}

const fsrscreen_lineDestinations = document.getElementsByClassName('fsrscreen_singleDepartureDestination');

/*for (let i = 0; i < fsrscreen_lineDestinations.length; i++) {
	adjustFontSize (fsrscreen_lineDestinations[i])
}*/