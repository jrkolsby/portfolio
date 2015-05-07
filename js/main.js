$(document).ready(function() {
	$('section.part').each(function() { //CSS workaround
		$(this).children('.wrap')
			   .children('article.small').each(function(i) {
			console.log(i);
			if (i%2 == 0) {
				$(this).addClass('margin');
			}
		});
	});
	$('article.full h1').each(function() {
		if ($(this).html().length > 16) {
			$(this).addClass('long');
		}
	})
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