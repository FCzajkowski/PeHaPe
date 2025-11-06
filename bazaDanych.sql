CREATE DATABASE IF NOT EXISTS test_database;
USE test_database;

CREATE TABLE IF NOT EXISTS uzytkownicy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
);

INSERT INTO uzytkownicy (username, password) VALUES ('root', 'root_password');
INSERT INTO uzytkownicy (username, password) VALUES ('user1', 'user1_password');
INSERT INTO uzytkownicy (username, password) VALUES ('cognati', 'super_tajne_haslo');

UPDATE uzytkownicy
SET password = 'nowe_bezpieczne_haslo'
WHERE username = 'user1';

DELETE FROM uzytkownicy WHERE username = 'root';

CREATE TABLE IF NOT EXISTS auta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marka VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    rok INT NOT NULL
);

INSERT INTO auta (marka, model, rok) VALUES ('Toyota', 'Corolla', 2020);
INSERT INTO auta (marka, model, rok) VALUES ('Honda', 'Civic', 2019);
INSERT INTO auta (marka, model, rok) VALUES ('BMW', 'M3', 2023);

UPDATE auta
SET rok = 2020
WHERE marka = 'Honda' AND model = 'Civic';

DELETE FROM auta WHERE marka = 'Toyota' AND model = 'Corolla';

SELECT * FROM uzytkownicy;
SELECT * FROM auta;

CREATE USER IF NOT EXISTS 'praktykant'@'localhost' IDENTIFIED BY '1234';
CREATE OR REPLACE VIEW uzytkownicy_public AS
SELECT id, username
FROM uzytkownicy;
GRANT SELECT ON test_database.uzytkownicy_public TO 'praktykant'@'localhost';
GRANT SELECT, INSERT, UPDATE ON test_database.auta TO 'praktykant'@'localhost';
GRANT USAGE ON *.* TO 'praktykant'@'localhost';
FLUSH PRIVILEGES;
