<?
require("phpQuery.php");
include("parseDown.php");
`git pull`

$postDir = "../content/";
$fileArray = scandir($postDir);
$postLog = json_decode(file_get_contents("postlog.json"), true);
$posts = [];
$parseDown = new Parsedown();
foreach ($fileArray as $key => $file) {
	$path = $postDir . "$file";
	$ext = pathinfo($path, PATHINFO_EXTENSION);
	$fileName = pathinfo($path, PATHINFO_FILENAME);
	if ($ext == "json" || $ext == "mdown") {
		$postInfo = json_decode(file_get_contents($path), true);
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
			$post['type'] = 1;
			//Make post
			$articleObject = phpQuery::newDocument("<article/>");
			pq("article")->addClass("full");
			$classArray = $postInfo['appearance']['class'];
			foreach ($classArray as $key => $class) {
				pq("article")->addClass($class);
			}
			pq("<div class='inner'/>")->appendTo("article")->append("<div class='info'/>");
			if (!$postInfo['appearance']['noTitle']) {
				$title = $postInfo['title'];
				pq("<h1/>")->appendTo("article .inner .info")
						   ->html($title);
			}
			$tag = $postInfo['tag'];
			pq("<h2/>")->appendTo("article .inner .info")
					   ->html($tag);
			foreach ($postInfo['links'] as $key => $link) {
				pq("<a/>")->attr("href", $link['url'])
							   ->append(pq("<button/>")->html($link['name']))
							   ->appendTo("article .inner .info");
			}
			$post['object'] = $articleObject;
		} else {
			$parse = $parseDown->text(file_get_contents($path));
			$articleObject = phpQuery::newDocument("<article/>");
			pq("article")->append($parse);
			pq("article h1")->wrap(pq("<div/>")
							->attr("style", "background: url('../content/" . $fileName . ".png')"));
			pq("article p")->wrap("<div/>");
			$isFilm = false;
			foreach (pq("ul li a") as $key => $link) {
				$name = pq($link)->html();
				$url = pq($link)->attr("href");
				if (strpos(strtolower($name), "video") !== false) {
					$isFilm = true;
				}
				pq("article div:last")->append(pq("<a/>")->attr("href", $url)->append(pq("<button/>")->html($name)));
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
/*
foreach ($posts as $key => $post) {
	print($post['object']);
}
*/
$updatedPostLog = json_encode($postLog);
file_put_contents("postlog.json", $updatedPostLog);
$template = phpQuery::newDocumentFileHTML('template.html');
?>