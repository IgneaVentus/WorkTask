let buttons;
let tabs;
let list = [];

function viewSelector(id) {
	buttons.forEach((button) => {
		if (button.id.includes(id)) button.classList.add("active");
		else button.classList.remove("active");
	})
	tabs.forEach((tab)=> {
		if (tab.id.includes(id)) tab.classList.remove("disabled");
		else tab.classList.add("disabled");
	})
}

function loadTop3Authors() {
	let spots = document.querySelectorAll("#top3 td");
	fetch("/API/topAuthors")
	.then((response) => {
		if(!response.ok) throw new Error("HTTP error! Status: "+response.status);
		return response.json();
	})
	.then((response) => {
		spots[0].innerHTML = ( response[0] ) ? response[0].name : "Wolne miejsce" ;
		spots[1].innerHTML = ( response[1] ) ? response[1].name : "Wolne miejsce" ;
		spots[2].innerHTML = ( response[2] ) ? response[2].name : "Wolne miejsce" ;
	})
}

// Header decides whether generator creates td or th elements 
function tableRowGenerator (data, header) {
	let result = document.createElement("tr");
	for (let i = 0; i < data.length; i++) {
		let buf;
		if (header) buf = document.createElement("th");
		else buf = document.createElement("td");
		buf.innerText = data[i];
		result.appendChild(buf);
	}
	return result;
}

function authorListPopulator () {
	list[1].appendChild(tableRowGenerator(["ID", "Nazwa"], true));
	fetch("/API/authors")
	.then((response) => {
		if(!response.ok) throw new Error("HTTP error! Status: "+response.status);
		return response.json();
	})
	.then((response) => {
		response.forEach(author => {
			list[1].appendChild(tableRowGenerator([author.id, author.name], false));
		});
	})
}

function articleListPopulator () {
	list[0].innerHTML = null;
	list[0].appendChild(tableRowGenerator(["ID", "Tytuł", "Treść", "Data utworzenia"], true));
	fetch("/API/articles")
	.then((response) => {
		if(!response.ok) throw new Error("HTTP error! Status: "+response.status);
		return response.json();
	})
	.then((response) => {
		response.forEach(article => {
			list[0].appendChild(tableRowGenerator([article.id, article.title, article.content, article.creation_date], false));
		});
	})
}

function articleSearch() {
	let id = document.querySelector("#newsSearch").value;
	let url;
	//Make sure whether we have a number or name and act accordingly
	if (!isNaN(id)) {
		if (id>0 && !(id%1)) url = "/API/articlesById?q="+id;
	}
	else url = "/API/articlesByAuthor?q="+id;
	fetch(url)
	.then((response) => {
		if(!response.ok) throw new Error("HTTP error! Status: "+response.status);
		return response.json();
	})
	.then((response) => {
		list[0].innerHTML = null;
		list[0].appendChild(tableRowGenerator(["ID", "Tytuł", "Treść", "Data utworzenia"], true));
		response.forEach(article => {
			list[0].appendChild(tableRowGenerator([article.id, article.title, article.content, article.creation_date], false));
		});
	})
}

function newArticleHandler() {
	form = document.querySelector("#newsEditor");
	form = new FormData(form);
	let authors = [];
	for (let i = 0; i<5; i++) {
		let buf = form.get("author_"+i);
		if (buf != null) authors.push(buf);
	}
	if ( authors.length == 0) {
		alert ("Wybierz autora!");
		return false;
	}
	if (form.get("id") == "" || form.get("title") == "" || form.get("content") == "") {
		alert ("Nie wszystkie pola wypełnione!");
		return false;
	}
	//let results = {"id": form.get("id"), "title": form.get("title"), "content": form.get("content"), "authors": authors};

	fetch("/API/articles", {
		method: "post",
		body: form
	})
	.then((response) => {
		if(!response.ok) throw new Error("HTTP error! Status: "+response.status);
		return response.json();
	})
	.then((response) => {
		if (response) {
			alert("SUKCES!");
			articleListPopulator();
			loadTop3Authors();
		}
		else alert("BŁĄD!");
	})
	return false;
}

window.onload = ()=>{
	tabs = document.querySelectorAll("#tabsContent > div")
	buttons = document.querySelectorAll("#tabs > span");
	buttons.forEach((button) => {
		button.addEventListener("click", e => {
			viewSelector(e.target.id);
		});
	});
	loadTop3Authors();
	let articleIDSearch = document.querySelector("#newsIdSearchButton");
	list[0] = document.querySelector("#newsListBody table.list");
	list[1] = document.querySelector("#authorsListBody table.list");
	authorListPopulator();
	articleListPopulator();
	document.querySelector("#newsSearchButton").addEventListener("click", articleSearch);
	document.querySelector("#newsEditBody .left").addEventListener("click", newArticleHandler);
}