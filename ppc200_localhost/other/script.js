$(function() {

$('.piece.unlocked').click(function() {
	if ($(this).html() === '') {
		$(this).html('1');
	} else {
		$(this).html( (parseInt($(this).html()) % 9) + 1 );
	}
});

$('#send').click(function() {
	var data = '';
	$('.piece').each(function() {
		data += ($(this).html() === '' ? '0' : $(this).html());
	});
	base = document.URL.split('?')[0];
	window.location = base + "?solution=" + data;
});

});