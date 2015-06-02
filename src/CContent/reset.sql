--
-- Create table for Content
--
DROP TABLE IF EXISTS Content;
CREATE TABLE Content
(
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
	idUser INT NOT NULL, 
    slug CHAR(80) UNIQUE,
    url CHAR(80) UNIQUE,

    type CHAR(80),
	category CHAR(80),
    title VARCHAR(80),
    data TEXT,
    filter CHAR(80),

    published DATETIME,
    created DATETIME,
    updated DATETIME,
    deleted DATETIME

) ENGINE INNODB CHARACTER SET utf8;

DELETE FROM Content;

INSERT INTO Content (idUser, slug, url, category, type, title, data, filter, published, created) VALUES
    (2, 'hem', 'hem', null, 'page', 'Hem', "Detta är min hemsida. Den är skriven i [url=http://en.wikipedia.org/wiki/BBCode]bbcode[/url] vilket innebär att man kan formattera texten till [b]bold[/b] och [i]kursiv stil[/i] samt hantera länkar.\n\nDessutom finns ett filter 'nl2br' som lägger in <br>-element istället för \\n, det är smidigt, man kan skriva texten precis som man tänker sig att den skall visas, med radbrytningar.", 'bbcode,nl2br', NOW(), NOW()),
    (2, 'om', 'om', null, 'page', 'Om', "Detta är en sida om mig och min webbplats. Den är skriven i [Markdown](http://en.wikipedia.org/wiki/Markdown). Markdown innebär att du får bra kontroll över innehållet i din sida, du kan formattera och sätta rubriker, men du behöver inte bry dig om HTML.\n\nRubrik nivå 2\n-------------\n\nDu skriver enkla styrtecken för att formattera texten som **fetstil** och *kursiv*. Det finns ett speciellt sätt att länka, skapa tabeller och så vidare.\n\n###Rubrik nivå 3\n\nNär man skriver i markdown så blir det läsbart även som textfil och det är lite av tanken med markdown.", 'markdown', NOW(), NOW()),
    (1, 'blogpost-1', null, 'Välkommen', 'post', 'Välkommen till min blogg!', "Detta är en bloggpost.\n\nNär det finns länkar till andra webbplatser så kommer de länkarna att bli klickbara.\n\nhttp://dbwebb.se är ett exempel på en länk som blir klickbar.", 'clickable,nl2br', NOW(), NOW()),
    (1, 'blogpost-2', null, 'Årstid', 'post', 'Nu har sommaren kommit', "Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost.", 'nl2br', NOW(), NOW()),
    (1, 'blogpost-3', null, 'Årstid', 'post', 'Nu har hösten kommit', "Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost", 'nl2br', NOW(), NOW())
;


--
-- Table for user
--
DROP TABLE IF EXISTS User;

CREATE TABLE User
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    acronym CHAR(12) UNIQUE NOT NULL,
    name VARCHAR(80),
    password CHAR(32),
    salt INT NOT NULL
) ENGINE INNODB CHARACTER SET utf8;

INSERT INTO User (acronym, name, salt) VALUES 
    ('doe', 'John/Jane Doe', unix_timestamp()),
    ('admin', 'Administrator', unix_timestamp())
;

UPDATE User SET password = md5(concat('doe', salt)) WHERE acronym = 'doe';
UPDATE User SET password = md5(concat('admin', salt)) WHERE acronym = 'admin';