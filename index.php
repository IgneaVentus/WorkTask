<?php
	namespace App;

	// Loading models and controllers
	require "app/controllers.php";
	require "app/models.php";

	// Preparation of routing variables
	$request_uri = explode("?", $_SERVER["REQUEST_URI"], 2);
	$request_tree = explode("/", trim($request_uri[0], " /"));
	$request_method = $_SERVER["REQUEST_METHOD"];

	// Routes
	switch ($request_tree[0]) {
		case "API": 
			switch ($request_tree[1]) {
				case "initialize": echo \Controllers\initializeDB(); break;
				case "topAuthors": echo \Controllers\top3Authors(); break;
				case "articlesById": echo \Controllers\articlesById($_GET["q"]); break;
				case "articlesByAuthor": echo \Controllers\articlesByAuthor($_GET["q"]); break;
				case "articles": 
					if ($request_method == "GET") {
						echo \Controllers\allArticles();
						break;
					}
					else if ($request_method == "POST") {
						echo \Controllers\addArticle();
						break;
					}
				case "authors": echo \Controllers\allAuthors(); break;
			}
			break;
		default: 
			return \Controllers\loadHome(); break;
	}
?>