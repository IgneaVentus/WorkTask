<?php
	namespace Controllers;
	use Models;
	use PDOException;
	use PDO;

	function loadNav() {
		$nav = [require "views/modular/nav.html", require "views/modular/footer.html"];
		return $nav;
	}

	function loadHome() {
		$nav = loadNav();
		$home = require "views/home.html";
		return $nav[0].$home.$nav[1];
	}

	function top3Authors() {
		$results = [];
		$conn = \Models\DBConnect(1);
		$stmt = $conn->prepare(
			"SELECT au.name, COUNT(ar.id) AS ArticlesNumber 
			FROM authors au LEFT JOIN articles_authors aa ON au.id = aa.authors_id 
			LEFT JOIN articles ar ON aa.articles_id = ar.id 
			WHERE ar.creation_date > SUBDATE(CURRENT_DATE(), INTERVAL 14 DAY) 
			GROUP BY au.name 
			ORDER BY ArticlesNumber DESC;");
		$stmt->execute();

		for ($i=0; $i<3; $i++) {
			array_push($results, $stmt->fetch(PDO::FETCH_ASSOC));
		}

		$conn = null;
		return json_encode($results);
	}

	function articlesById($id) {
		$buf = new \Models\Articles();
		$buf->read($id);

		return (json_encode([$buf->represent()]));
	}

	function articlesByAuthor($name) {
		$results = [];
		$name = strip_tags($name);
		str_replace("[]()\\/!?,.;:\'\"@#$%^&*+-", "", $name);
		$article = new \Models\Articles();
		$conn = \Models\DBConnect(1);
		$stmt = $conn->prepare(
			'SELECT aa.articles_id
			FROM authors au
			LEFT JOIN articles_authors aa ON au.id = aa.authors_id
			WHERE au.name LIKE "%'.$name.'%"
			ORDER BY aa.articles_id'
		);

		$stmt->execute();
		$idArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($idArray as $buf) {
			$article->read($buf["articles_id"]);
			array_push($results, $article->represent());
		}

		$conn = null;
		return json_encode($results);
	}

	function allAuthors() {
		$conn = \Models\DBConnect(1);
		$stmt = $conn->prepare("SELECT * FROM authors");
		$stmt->execute();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$conn = null;
		return json_encode($results);
	}

	function allArticles() {
		$conn = \Models\DBConnect(1);
		$stmt = $conn->prepare("SELECT * FROM articles");
		$stmt->execute();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$conn = null;
		return json_encode($results);
	}

	function addArticle() {
		$id = $_POST["id"];
		$title = $_POST["title"];
		$content = $_POST["content"];
		$authors = [];
		for($i=1; $i<5; $i++) {
			if (isset($_POST["author_".$i])) array_push($authors, $_POST["author_".$i]);
		}

		$checker = 0;

		$article = new \Models\Articles();

		if ($id != 0) {
			$article->read($id);
			$article->title = $title;
			$article->content = $content;
			$buf = $article->update();
			if (is_numeric($buf)) $checker++;
			else return $buf;
		}

		else {
			$article->title = $title;
			$article->content = $content;
			$effect = $article->create();
			if (is_numeric($effect)) $checker++;
			else return $effect;
		}

		foreach ($authors as $author) {
			$buffer = new \Models\Articles_Authors();
			$buffer->articles_id = $article->id;
			$buffer->authors_id = $author;
			if (is_numeric($buffer->create())) $checker++;
		}

		if ($checker == count($authors)+1) return 1;
		else return 0;
	}

	/* Database reinitializer - should be ran when first running project. 
	1) Try to create database
	2) Drop and re-add required tables
	3) Fill them up with example data */
	function initializeDB () {
		$log = [];
		$i = 0;
		$log[$i++] = "Beggining initialization!<br/>";
		$data = json_decode(file_get_contents("config.json"))->database;
		$conn = Models\DBConnect(0);
		if (is_array($conn)) return "Cannot connect to server at all";
		else $log[$i++] = "Connection to server secured<br/>";
		$sql = "CREATE DATABASE IF NOT EXISTS $data->dbname";
		try {
			$conn->exec($sql);
			$log[$i++] = "Database Created<br/>";
		}
		catch (PDOException $e) {
			echo $e->getMessage();
			return 0;
		}
		$conn = Models\DBConnect(1);
		if (is_array($conn)) return "Cannot connect to server at all";
		else $log[$i++] = "Connection to database secured<br/>";
		
		try {
			$conn->exec("DROP TABLE IF EXISTS articles_authors");
			$conn->exec("DROP TABLE IF EXISTS authors");
			$conn->exec("DROP TABLE IF EXISTS articles");
			$log[$i++] = "Dropped old tables (if any)!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot drop old tables. ".$e->getMessage();
			return 0;
		}

		try {
			$conn->exec(
				"CREATE TABLE articles (
					id INT NOT NULL AUTO_INCREMENT,
					title TEXT NOT NULL,
					content TEXT NOT NULL,
					creation_date DATE NOT NULL,
					PRIMARY KEY (id)
				)"
			);
			$log[$i++] = "Added articles table!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot setup articles table. ".$e->getMessage();
			return 0;
		}
		
		try {
			$conn->exec(
				"CREATE TABLE authors (
					id INT NOT NULL AUTO_INCREMENT,
					name TEXT NOT NULL,
					PRIMARY KEY (id)
				)"
			);
			$log[$i++] = "Added authors table!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot setup authors table. ".$e->getMessage();
			return 0;
		}
		
		try {
			$conn->exec(
				"CREATE TABLE articles_authors (
					authors_id INT NOT NULL,
					articles_id INT NOT NULL,
					FOREIGN KEY (authors_id) REFERENCES authors(id) ON DELETE CASCADE,
					FOREIGN KEY (articles_id) REFERENCES articles(id) ON DELETE CASCADE
				)"
			);
			$log[$i++] = "Added articles_authors table!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot setup articles_authors table. ".$e->getMessage();
			return 0;
		}

		try {
			$conn->exec(
				'INSERT INTO articles
				(title, content, creation_date) 
				VALUES
				("Skok cen kurczaka w Wejcherowie!", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam turpis dolor, porta nec nunc ac, semper faucibus diam. Pellentesque nulla sem, fermentum id luctus non, dignissim eget metus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nam sagittis mi vel interdum dapibus. Curabitur finibus viverra eleifend. Nulla mollis mollis nunc, in scelerisque purus ornare et. Integer massa dui, mollis vitae aliquam posuere, porttitor sit amet diam. Praesent mauris lorem, gravida nec fringilla pulvinar, porta sed enim. Quisque auctor tortor ante, at accumsan magna gravida tempus. In ut lacinia arcu. Fusce metus magna, commodo quis scelerisque vitae, gravida eu purus.", "2023-02-10"),
				("Oszalały fan wbiegł na murawę w Gdańsku!", "Vivamus vel commodo nulla. Nulla facilisi. Maecenas aliquet, urna eget laoreet egestas, ex orci ultrices libero, sed convallis enim turpis eget purus. Etiam tempus porta fringilla. Vivamus viverra sapien sed venenatis tempor. Aenean auctor sem urna, nec eleifend mauris mollis tempus. Aliquam fermentum rutrum fermentum. Morbi eget magna et tellus auctor pretium. In hac habitasse platea dictumst. Morbi fringilla lorem orci, in dignissim enim egestas at. Pellentesque mollis, nunc sed sollicitudin feugiat, felis velit rhoncus tellus, nec lacinia urna nunc a diam. Ut magna sem, suscipit in efficitur vitae, molestie vitae diam.", "2023-02-22"),
				("Czy obcy są wśród nas?!", "Suspendisse et ipsum sodales, laoreet sapien quis, condimentum ipsum. Curabitur malesuada justo non augue finibus cursus. Praesent fermentum enim sit amet fringilla porttitor. Nam hendrerit fringilla ornare. Nulla at justo fermentum, porta purus et, porta odio. Vestibulum in neque at dui volutpat dictum non a urna. Proin tempus arcu turpis, sit amet vulputate felis pharetra a. In a nunc vulputate, luctus neque at, gravida quam. Donec in congue odio. Vivamus odio mauris, consequat et ultrices varius, maximus tincidunt velit. Aenean eleifend laoreet sem eu porta. Duis faucibus neque ex. Fusce bibendum dui lectus, eu fermentum nisl tincidunt ut.", "2023-02-26");
				'
			);
			$log[$i++] = "Inserted values into articles table!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot insert values into articles table. ".$e->getMessage();
			return 0;
		}

		try {
			$conn->exec(
				'INSERT INTO authors (name) 
				VALUES ("Adam Mierzejski"), ("Krystyna Żak"), ("Michał Zakolski"), ("Karolina Murewicz");'
			);
			$log[$i++] = "Inserted values into authors table!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot insert values into authors table. ".$e->getMessage();
			return 0;
		}

		try {
			$conn->exec(
				'INSERT INTO articles_authors (articles_id, authors_id)
				VALUES (1, 1), (2, 1), (3,2);'
			);
			$log[$i++] = "Inserted values into articles_authors table!<br/>";
		}
		catch (PDOException $e) {
			echo "ERROR: Cannot insert values into articles_authors table. ".$e->getMessage();
			return 0;
		}


		$log[$i++] = "Initialization complete!<br/>";

		$conn = null;
		return(json_encode($log));
	}
?>