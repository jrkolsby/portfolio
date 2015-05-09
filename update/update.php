<?
require("phpQuery.php");
include("parseDown.php");
print exec('git pull origin master');

$postDir = "../content/";
$fileArray = scandir($postDir);
$postLog = json_decode(file_get_contents("postlog.json"), true);
$posts = array();
$parseDown = new Parsedown();
foreach ($fileArray as $key => $file) {
	$path = $postDir . "$file";
	$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
	$fileName = strtolower(pathinfo($path, PATHINFO_FILENAME));
	if ($ext == "json" || 
		$ext == "mdown" || 
		$ext == "md") {
		$post = array(
			'filename' => $fileName,
		);
		if (array_key_exists($fileName, $postLog)) {
			$post['date'] = $postLog[$fileName];
		} else {
			$post['date'] = (int)date('Ymd');
			$postLog[$fileName] = $post['date'];
		}
		if ($ext == "json") {
			$postInfo = json_decode(file_get_contents($path), true);
			$post['type'] = 1;
			//Make post
			$articleObject = phpQuery::newDocument("<article/>");
			pq("article")->addClass("full");
			$classArray = $postInfo['appearance']['class'];
			foreach ($classArray as $key => $class) {
				pq("article")->addClass($class);
			}
			pq("<div class='inner'/>")->appendTo("article")->append("<div class='info'/>");
			if (pq("article")->hasClass('background')) {	
				pq("article")->attr("style", "background: url('content/" . $fileName . ".png') no-repeat scroll center center #fff");
			} else if (pq("article")->hasClass('side')) {
				pq("<img/>")->attr("src", "content/" . $fileName . ".png")
							->insertAfter("div.info");
			} else if (pq("article")->hasClass('center')) {
				pq("<img/>")->attr("src", "content/" . $fileName . ".png")
							->insertBefore("div.info");
				pq("<br/>")->insertAfter("img");
			}
			if (!!$postInfo['appearance']['backgroundGradient']) {
				pq("article")->attr("style", "background: linear-gradient(to bottom, #" . $postInfo['appearance']['backgroundGradient'][0] . 
											 " 0%, #" . $postInfo['appearance']['backgroundGradient'][1] . 
											 " 70%) repeat scroll 0% 0% transparent");
			}
			if (!!$postInfo['appearance']['align']) {
				pq("article .inner .info")->attr("style", "text-align: " . $postInfo['appearance']['align'] . ";");
			}
			if (!!$postInfo['title']) {
				$title = $postInfo['title'];
				pq("<h1/>")->appendTo("article .inner .info")
						   ->html($title);
				if (!!$postInfo['appearance']['titleColor']) {
					pq(".info h1")->attr("style", "color: #" . $postInfo['appearance']['titleColor'] . ";");
				}
			}
			$tag = $postInfo['tag'];
			pq("<h2/>")->appendTo("article .inner .info")
					   ->html($tag);
			foreach ($postInfo['links'] as $key => $link) {
				pq("<a/>")->attr("href", $link['url'])
							   ->append(pq("<button/>")->html($link['name']))
							   ->appendTo("article .inner .info");
				if (!!$postInfo['appearance']['buttonColor']) {
					pq("a button")->attr("style", "border: 2.5px solid #" . $postInfo['appearance']['buttonColor'] .
										 "; color: #" . $postInfo['appearance']['buttonColor'] . ";");
				}
			}
			$post['object'] = $articleObject;
		} else if ($ext == "mdown" ||
				   $ext == "md") {
			//Make post
			$parse = $parseDown->text(file_get_contents($path));
			$articleObject = phpQuery::newDocument("<article/>");
			pq("article")->append($parse);
			pq("article h1")->wrap(pq("<div/>")
							->attr("style", "background: url('content/" . $fileName . ".png')"));
			pq("article p")->wrap("<div/>");
			$isFilm = false;
			foreach (pq("ul li a") as $key => $link) {
				$name = pq($link)->html();
				$url = pq($link)->attr("href");
				if (strpos(strtolower($name), "film") !== false) {
					$isFilm = true;
				}
				pq("article div:last")->append(pq("<a/>")->attr("href", $url)
														 ->append(pq("<button/>")->html($name)));
			}
			pq("article ul")->remove();
			if ($isFilm) {
				pq("article")->addClass("film");
				$post['type'] = 2;
			} else {
				pq("article")->addClass("small");	
				$post['type'] = 3;	
			}
			$post['object'] = $articleObject;
		}
		array_push($posts, $post);
	}
}
$updatedPostLog = json_encode($postLog);
file_put_contents("postlog.json", $updatedPostLog);
function comparePosts($a, $b) {
	if ($a['date'] == $b['date']) {
    	return 0;
	}
	return ($a['date'] > $b['date']) ? -1 : 1;
}
usort($posts, "comparePosts");
$template = phpQuery::newDocumentFileHTML('template.html');
$partSection = array();
$shouldAppendPartSection;
foreach ($posts as $key => $post) {
	switch ($post['type']) {
		case 1:
			pq("main")->append($post['object']);
			break;
		case 2:
			array_unshift($partSection, $post);
			break;
		default:
			array_push($partSection, $post);
			break;
	}
	if (count($partSection) > 1 &&
		$key == count($posts)-1) {
		$shouldAppendPartSection = true;
	} else if (!empty($partSection) && 
			   $posts[$key+1]['type'] == 1) {
		$shouldAppendPartSection = true;
	} else {
		$shouldAppendPartSection = false;
	}
	if ($shouldAppendPartSection) {
		pq("<section/>")->addClass("part")
						->append(pq("<div/>")->addClass("wrap"))
						->appendTo("main");
		foreach ($partSection as $partPost) {
			pq("section.part:last .wrap")->append($partPost['object']);
		}
		unset($partSection);
		$partSection = array();
	}
}
foreach (pq("section.part") as $section) {
	foreach (pq($section)->children('.wrap')
						 ->children('article.small') as $key => $small) {
		if ($key%2 == 0) {
			pq($small)->addClass('margin');
		}
	}
}
foreach (pq('article.full h1') as $title) {
	if (strlen(pq($title)->html()) > 16) {
		pq($title)->addClass('long');
	}
}
file_put_contents("../index.html", $template);
?>