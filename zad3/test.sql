-- Autor: Marek Górski
-- nr indeksu: 155647
-- grupa D1
-- rok akademicki 2024/2025
-- semestr V

CREATE DATABASE test;
USE test;

CREATE USER 'int_baz'@'localhost' IDENTIFIED BY '1nt3rn3t0w3_b4zy';

CREATE TABLE `audit_subscribers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
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

GRANT INSERT, UPDATE, DELETE, SELECT, TRIGGER ON test.* TO 'int_baz'@'localhost';
GRANT INSERT, UPDATE, DELETE, SELECT, TRIGGER ON test.* TO 'int_baz'@'localhost';
FLUSH PRIVILEGES;

DELIMITER $$
CREATE TRIGGER `after_subscriber_delete` AFTER DELETE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (user_id, subscriber_name, action_performed)
	VALUES (OLD.id, OLD.fname, 'Deleted a subscriber');
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `after_subscriber_edit` AFTER UPDATE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (user_id, subscriber_name, action_performed)
    VALUES (OLD.id, NEW.fname, 'Updated a subscriber');
END
$$
DELIMITER ;

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

ALTER TABLE `audit_subscribers`
  ADD PRIMARY KEY (`id`);
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `subscribers`
  AUTO_INCREMENT = 1;

ALTER TABLE `audit_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `audit_subscribers`
  AUTO_INCREMENT = 1;

-- pkt. 1 -> widok wyświetlający nazwę użytkowników oraz datę ich dodania
CREATE VIEW user_creation_view AS
SELECT
  subscriber_name,
  date_added AS creation_date
FROM
  audit_subscribers
WHERE
  action_performed = 'Insert a new subscriber';

-- pkt. 2 -> widok wyświetlający nazwę użytkowników oraz datę ich usunięcia
CREATE VIEW user_deletion_view AS
SELECT
  subscriber_name,
  date_added AS deletion_date
FROM
  audit_subscribers
WHERE
  action_performed = 'Deleted a subscriber';

-- pkt. 3 -> widok wyświetlający nazwę użytkowników oraz datę ich edycji
CREATE VIEW user_edit_view AS
SELECT
  subscriber_name,
  date_added AS edit_date
FROM
  audit_subscribers
WHERE
  action_performed = 'Updated a subscriber';

-- pkt. 4 -> widok wyświetlający nazwę już usuniętych użytkowników oraz daty ich dodania i usunięcia
CREATE VIEW deleted_users_history_view AS
SELECT
  user_id,
  subscriber_name,
  action_performed,
  date_added as action_date
FROM
  audit_subscribers
WHERE
  user_id IN (
    SELECT DISTINCT user_id
    FROM audit_subscribers
    WHERE action_performed = 'Deleted a subscriber'
  )
ORDER BY
  user_id, action_date;


-- pkt. 5 -> widok wyświetlający tylko użytkowników istniejących
CREATE VIEW existing_users_view AS
SELECT DISTINCT
  user_id,
  subscriber_name
FROM
  audit_subscribers
WHERE
  user_id NOT IN (
    SELECT user_id
    FROM audit_subscribers
    WHERE action_performed = 'Deleted a subscriber'
  );