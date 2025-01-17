-- Autor: Marek Górski
-- nr indeksu: 155647
-- grupa D1
-- rok akademicki 2024/2025
-- semestr V

-- stworzenie bazy danych
CREATE DATABASE test;
-- użycie stworzonej bazy
USE test;

-- stworzenie użytkownika i przypisanie hasła
CREATE USER 'int_baz'@'localhost' IDENTIFIED BY '1nt3rn3t0w3_b4zy';


-- stworzenie tablic z odpowiednimi polami
CREATE TABLE `audit_subscribers` (
  `id` int(11) NOT NULL,
  `user_id` int (11) DEFAULT NULL,
  `subscriber_name` varchar(255) NOT NULL,
  `action_performed` text NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
);

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
);

-- przyznanie uprawnień użytkownikowi do tej bazy danych
GRANT INSERT, UPDATE, DELETE, SELECT, TRIGGER ON test.* TO 'int_baz'@'localhost';
FLUSH PRIVILEGES;


-- trigger `after_subscriber_delete` dodający informację o usunięciu użytkownika
DELIMITER $$
CREATE TRIGGER `after_subscriber_delete` AFTER DELETE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (user_id, subscriber_name, action_performed)
	VALUES (OLD.id, OLD.fname, 'Deleted a subscriber');
END
$$
DELIMITER ;

-- trigger `after_subscriber_edit` rejestrujący akcję edycji
DELIMITER $$
CREATE TRIGGER `after_subscriber_edit` AFTER UPDATE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (id, subscriber_name, action_performed)
    VALUES (OLD.id, NEW.fname, 'Updated a subscriber');
END
$$
DELIMITER ;

-- trigger `before_subscriber_insert` rejestrujący przed dodaniem, informację o dodaniu nowego usera
-- jest napisany w dość skomplikowany sposób przez dziwne wymogi zadania trzeciego
DELIMITER $$
CREATE TRIGGER `before_subscriber_insert` BEFORE INSERT ON `subscribers` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(user_id), 0) INTO last_id FROM audit_subscribers;
  SET last_id = last_id + 1;

  INSERT INTO audit_subscribers (user_id, subscriber_name, action_performed)
  VALUES (last_id, NEW.fname, 'Insert a new subscriber');
END
$$
DELIMITER ;

-- dodanie klucza głównego tabeli `audit_subscribers`
ALTER TABLE `audit_subscribers`
  ADD PRIMARY KEY (`id`);

-- dodanie klucza głównego tabeli `subscribers`
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

-- zmiana parametrów klucza głównego - nie może być pusty i jest automatycznie inkrementowany
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- liczenie zaczynam od 1
ALTER TABLE `subscribers`
  AUTO_INCREMENT = 1;

-- zmiana parametrów klucza głównego - nie może być pusty i jest automatycznie inkrementowany
ALTER TABLE `audit_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- liczenie zaczynam od 1
ALTER TABLE `audit_subscribers`
  AUTO_INCREMENT = 1;