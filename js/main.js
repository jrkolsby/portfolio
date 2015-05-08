$(document).ready(function() {
	$('section#about, main').addClass("top");
	$(window).scroll(function() {
		if ($(window).scrollTop() > 10 && $("section#about a:last-child").offset().top
										  + $("section#about a:last-child").height() + 20
										  < $(window).scrollTop() + $(window).height()) {
			$('section#about, main').removeClass("top");
		} else {
			$('section#about, main').addClass("top");
		}
	});
	$('section.part article div:first-child').click(function() {
		$(this).parent().toggleClass('active');
	});
});