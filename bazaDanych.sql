CREATE DATABASE IF NOT EXISTS test_database;
USE test_database;

CREATE TABLE IF NOT EXISTS auta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marka VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    rok INT NOT NULL
);

INSERT INTO auta (marka, model, rok) VALUES ('Toyota', 'Corolla', 2020);
INSERT INTO auta (marka, model, rok) VALUES ('Honda', 'Civic', 2019);
INSERT INTO auta (marka, model, rok) VALUES ('BMW', 'M3', 2023);
