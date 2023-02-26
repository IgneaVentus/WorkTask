DROP TABLE IF EXISTS articles_authors;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS authors;

CREATE TABLE articles (
    id INT NOT NULL AUTO_INCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    creation_date DATE NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE authors (
    id INT NOT NULL AUTO_INCREMENT,
    name TEXT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE articles_authors (
    authors_id INT NOT NULL,
    articles_id INT NOT NULL,
    FOREIGN KEY (authors_id)
        REFERENCES authors (id)
        ON DELETE CASCADE,
    FOREIGN KEY (articles_id)
        REFERENCES articles (id)
        ON DELETE CASCADE
);

INSERT INTO articles
(title, content, creation_date) 
VALUES
("Skok cen kurczaka w Wejcherowie!", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam turpis dolor, porta nec nunc ac, semper faucibus diam. Pellentesque nulla sem, fermentum id luctus non, dignissim eget metus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nam sagittis mi vel interdum dapibus. Curabitur finibus viverra eleifend. Nulla mollis mollis nunc, in scelerisque purus ornare et. Integer massa dui, mollis vitae aliquam posuere, porttitor sit amet diam. Praesent mauris lorem, gravida nec fringilla pulvinar, porta sed enim. Quisque auctor tortor ante, at accumsan magna gravida tempus. In ut lacinia arcu. Fusce metus magna, commodo quis scelerisque vitae, gravida eu purus.", "2023-02-10"),
("Oszalały fan wbiegł na murawę w Gdańsku!", "Vivamus vel commodo nulla. Nulla facilisi. Maecenas aliquet, urna eget laoreet egestas, ex orci ultrices libero, sed convallis enim turpis eget purus. Etiam tempus porta fringilla. Vivamus viverra sapien sed venenatis tempor. Aenean auctor sem urna, nec eleifend mauris mollis tempus. Aliquam fermentum rutrum fermentum. Morbi eget magna et tellus auctor pretium. In hac habitasse platea dictumst. Morbi fringilla lorem orci, in dignissim enim egestas at. Pellentesque mollis, nunc sed sollicitudin feugiat, felis velit rhoncus tellus, nec lacinia urna nunc a diam. Ut magna sem, suscipit in efficitur vitae, molestie vitae diam.", "2023-02-22"),
("Czy obcy są wśród nas?!", "Suspendisse et ipsum sodales, laoreet sapien quis, condimentum ipsum. Curabitur malesuada justo non augue finibus cursus. Praesent fermentum enim sit amet fringilla porttitor. Nam hendrerit fringilla ornare. Nulla at justo fermentum, porta purus et, porta odio. Vestibulum in neque at dui volutpat dictum non a urna. Proin tempus arcu turpis, sit amet vulputate felis pharetra a. In a nunc vulputate, luctus neque at, gravida quam. Donec in congue odio. Vivamus odio mauris, consequat et ultrices varius, maximus tincidunt velit. Aenean eleifend laoreet sem eu porta. Duis faucibus neque ex. Fusce bibendum dui lectus, eu fermentum nisl tincidunt ut.", "2023-02-26");

INSERT INTO authors (name) 
VALUES ("Adam Mierzejski"), ("Krystyna Żak"), ("Michał Zakolski"), ("Karolina Murewicz");

INSERT INTO articles_authors (articles_id, authors_id)
VALUES (1, 1), (2, 1), (3,2);