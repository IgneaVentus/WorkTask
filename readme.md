# Obsługa

## Plik konfiguracyjny cofing.json

Zawiera dane używane przez aplikację do połączenia się z serwerem MySQL

## Plik wsadowy run.bat

Przy pliku wykonawczym php dodanym do ścieżki systemu spróbuje automatycznie odpalić przeglądarkę na odpowiednim localhoście i wystartować wbudowany w php serwer developerski.

## /

Strona HTML z formularzem i podglądem tablic artykułów oraz autorów, wraz z top 3 autorami na szczycie.

## /API/initialize - Inicjalizacja bazy danych

Spowoduje to próbę stworzenia bazy danych oraz początkowej inicjalizacji jej zawartości do celów pokazowych. W razie błędu w głównym folderze znajduje się plik SQL z tymi samymi instrukcjami.

## /API/topAuthors

Zwraca 3 pierwsze rekordy z tabeli authors bazując na przeliczonej ilości powiązanych artykółów.

## /API/articlesById?q=<id>

Pozwala uzyskać artykół o konkretnie podanym za pomocą metody GET numerze id. (domyślnie w bazie danych są jedynie pozucje od 1 do 3)

## /API/articlesByAuthor?q=<name>

Pozwala uzyskać wszystkie artykuły powiązane z konkretnym autorem. Argument podawany w parametrze p może być częścią nazwy.

## /API/articles

W zależności od użytej metody albo udostępnia wszystkie obecnie dostępne artykuły (GET) albo pozwala dodać nowy artykół (POST).

Przy dodaniu artykułu wymaga wysłania obiektu FormData zawierającego parametry: id, title, content oraz author_1 do author_4 odpowiadające kolejno numerom ID autorów od 1 do 4.

W wypadku parametru id wynoszącego zero, aplikacja stworzy nowy artykół - jeśli id jednak ma podaną konkretną liczbę, aplikacja podejmię próbę zaktualizowania już obecnego rekordu.

## /API/authors

Udostępnia wszystkie rekordy z tablicy authors