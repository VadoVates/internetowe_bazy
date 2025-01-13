-- Autor: Marek Górski
-- nr indeksu: 155647
-- grupa D1
-- rok akademicki 2024/2025
-- semestr V

CREATE DATABASE IF NOT EXISTS chart;

USE chart;

CREATE USER IF NOT EXISTS 'int_baz'@'localhost' IDENTIFIED BY '1nt3rn3t0w3_b4zy';

GRANT ALL PRIVILEGES ON chart.* TO 'int_baz'@'localhost';

CREATE TABLE IF NOT EXISTS history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_time DATETIME NOT NULL,
    EURbuy FLOAT NOT NULL,
    EURsell FLOAT NOT NULL,
    USDbuy FLOAT NOT NULL,
    USDsell FLOAT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL
);

FLUSH PRIVILEGES;