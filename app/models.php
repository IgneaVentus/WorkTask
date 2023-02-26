<?php
namespace Models;


use Exception;
use PDO;
use PDOException;

// To avoid repeatition, connection booted out to it's own function.
// Mode responds to 0 for server and 1 for database itself
// For connection configuration uses config.json in main directory.
function DBConnect ($mode) {
	$data = json_decode(file_get_contents("config.json"))->database;
	try {
		if ($mode == 1) $conn = new PDO ("mysql:host=$data->host;dbname=$data->dbname", $data->username, $data->password, array(PDO::ATTR_PERSISTENT => true));
		else if ($mode == 0 ) $conn = new PDO ("mysql:host=$data->host", $data->username, $data->password, array(PDO::ATTR_PERSISTENT => true));
		$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		return $conn;
	}
	catch (PDOException $e) {
		$buf = $e->getMessage();
		if (str_contains($buf, `Unkown database`)) {
			echo "Warning: Database not found. Running initialization may help with that.";
		}
		return [0, $buf];
	}
}

// Query variable is preprepared statement for prepare, data is an array with all parameters in it
// In case of success, function returns ID of new row. Else it return error message.
function inserter ($query, $data) {
	$conn = DBConnect(1);
	if (is_array($conn)){
		if ($conn[0]==0) return $conn[1];
	}
	$stmt = $conn->prepare($query);
	try {
		$conn->beginTransaction();
		if (!$stmt->execute($data)) throw new Exception ("Błąd podczas próby stworzenia obiektu.");
		$id = $conn->lastInsertId();
		if (!$conn->commit()) throw new Exception ("Błąd podczas próby stworzenia obiektu");
	}
	catch (\Throwable $th) {
		$conn->rollBack();
		return json_encode([$th->getMessage(), $data]);
	}
	$conn = null;
	return $id;
}

function deleter ($table, $id) {
	$conn = DBConnect(1);
	if ($conn[0]==0) return $conn[1];
	$query = "DELETE FROM ".$table." WHERE id = ?";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(1, $id);
	try {
		$conn->beginTransaction();
		if (!$stmt->execute()) throw new Exception ("Błąd podczas próby stworzenia obiektu.");
	}
	catch (\Throwable $th) {
		$conn->rollBack();
		return $th->getMessage();
	}
	$conn = null;
	return 1;
}

interface iDBOject {
	function __get($name);
	function __set($name, $value);
	function create();
	function read($id);
	function update();
}

class Articles implements iDBOject {
	private $id = null;
	private $title = null;
	private $content = null;
	private $creation_date = null;
	private $createQuery = "INSERT INTO articles (title, content, creation_date) VALUES (?,?,?)";
	private $updateQuery = "UPDATE articles SET title = ?, content = ? WHERE id = ?";
	
	function __construct () {
		$this->creation_date = date("Y-m-j", time());
	}

	function __get ($name) {
		switch($name) {
			case "id": return $this->id;
			case "title": return $this->title;
			case "content": return $this->content;
			case "creation_date": return $this->creation_date;
		}
	}

	function __set ($name, $value) {
		switch ($name) {
			case "id":
				if (is_numeric($value)) {
					$this->id = (int) $value;
					return 1;
				}
			case "title": $this->title = htmlspecialchars($value); return 1;
			case "content": $this->content = htmlspecialchars($value); return 1;
		}
	}

	function represent () {
		return ["id" => $this->id, "title" => $this->title, "content" => $this->content, "creation_date" => $this->creation_date];
	}

	function create () {
		if (!isset($this->title) || !isset($this->content)) return "Not every field filled";
		$data = inserter ($this->createQuery, 
		[
			$this->title,
			$this->content,
			$this->creation_date
		]);
		if (is_numeric($data)) $this->id = (int) $data;
		return $data;
	}

	function read ($id) {
		$conn = DBConnect(1);
		$stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
		$stmt->bindParam(1, $id);
		$stmt->execute();

		$buf = $stmt->fetch(PDO::FETCH_ASSOC);
		$conn = null;

		$this->id = $buf["id"];
		$this->title = $buf["title"];
		$this->content = $buf["content"];
		$this->creation_date = $buf["creation_date"];
		return 1;
		return 1;
	}

	function update () {
		if (!isset($this->id) || !isset($this->title) || !isset($this->content)) return "Not every field filled";
		$data = inserter ($this->updateQuery, 
		[
			$this->title,
			$this->content,
			$this->id
		]);
		if (is_numeric($data)) $this->id = (int) $data;
		return $data;
	}
}

class Authors implements iDBOject {
	private $id = null;
	private $name = null;
	private $createQuery = "INSERT INTO authors (name) VALUES (?)";
	private $updateQuery = "UPDATE authors SET name = ? WHERE id = ?";
	
	function __construct () {
		$this->creation_date = date("Y-m-j", time());
	}

	function __get ($name) {
		switch($name) {
			case "id": return $this->id;
			case "name": return $this->name;
		}
	}

	function __set ($name, $value) {
		switch ($name) {
			case "id":
				if (is_numeric($value)) {
					$this->id = (int) $value;
					return 1;
				}
				else return 0;
			case "name": $this->name = htmlspecialchars($value) ; return 1;
		}
	}

	function create () {
		if (!isset($this->name)) return "Not every field filled";
		$data = inserter ($this->createQuery, 
		[
			$this->name
		]);
		if (is_numeric($data)) $this->id = (int) $data;
		return $data;
	}

	function read ($id) {
		$conn = DBConnect(1);
		$stmt = $conn->prepare("SELECT * FROM authors WHERE id = ?");
		$stmt->bindParam(1, $id);
		$stmt->execute();

		$buf = $stmt->fetch(PDO::FETCH_ASSOC);
		$conn = null;

		$this->id = $buf["id"];
		$this->name = $buf["name"];
		return 1;
	}

	function update () {
		if (!isset($this->id) || !isset($this->name)) return "Not every field filled";
		$data = inserter ($this->updateQuery, 
		[
			$this->name,
			$this->id
		]);
		if (is_numeric($data)) $this->id = (int) $data;
		return $data;
	}
}

class Articles_Authors implements iDBOject {
	private $articles_id = null;
	private $authors_id = null;
	private $createQuery = "INSERT INTO articles_authors (articles_id, authors_id) VALUES (?,?)";
	private $updateQuery = "UPDATE articles_authors SET articles_id = ?, authors_id = ? WHERE id = ?";

	function __get ($name) {
		switch($name) {
			case "articles_id": return $this->articles_id;
			case "authors_id": return $this->authors_id;
		}
	}

	function __set ($name, $value) {
		switch ($name) {
			case "articles_id":
				if (is_numeric($value)) {
					$this->articles_id = (int) $value;
					return 1;
				}
				else return 0;
			case "authors_id":
				if (is_numeric($value)) {
					$this->authors_id = (int) $value;
					return 1;
				}
				else return 0;
		}
	}

	function create () {
		if (!isset($this->articles_id) || !isset($this->authors_id)) return "Not every field filled";
		inserter ($this->createQuery, 
		[
			$this->articles_id,
			$this->authors_id,
		]);
		return 1;
	}


	function read ($id) {
		return null;
	}

	function update () {
		return null;
	}
}

?>