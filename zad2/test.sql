-- Autor: Marek Górski
-- nr indeksu: 155647
-- grupa D1
-- rok akademicki 2024/2025
-- semestr V

CREATE DATABASE test;
USE test;

CREATE USER 'int_baz'@'localhost' IDENTIFIED BY '1nt3rn3t0w3_b4zy';

CREATE TABLE `audit_subscribers` (
  `audit_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `subscriber_name` VARCHAR(255) NOT NULL,
  `action_performed` TEXT NOT NULL,
  `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
);

GRANT INSERT, UPDATE, DELETE, SELECT ON test.* TO 'int_baz'@'localhost';
FLUSH PRIVILEGES;

DELIMITER $$
CREATE TRIGGER `after_subscriber_delete` AFTER DELETE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (id, subscriber_name, action_performed)
	VALUES (OLD.id, OLD.fname, 'Deleted a subscriber');
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `after_subscriber_edit` AFTER UPDATE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (id, subscriber_name, action_performed)
    VALUES (OLD.id, NEW.fname, 'Updated a subscriber');
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `before_subscriber_insert` BEFORE INSERT ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (id, subscriber_name, action_performed)
    VALUES (NEW.id, NEW.fname, 'Insert a new subscriber');
END
$$
DELIMITER ;

-- wrzucenie ID użytkownika w after subscriber insert
DELIMITER $$
CREATE TRIGGER `after_subscriber_insert` AFTER DELETE ON `subscribers` FOR EACH ROW BEGIN
	INSERT INTO audit_subscribers (id, subscriber_name, action_performed)
	VALUES (OLD.id, OLD.fname, 'Deleted a subscriber');
END
$$
DELIMITER ;

ALTER TABLE `audit_subscribers`
  ADD PRIMARY KEY (`audit_id`);

ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `audit_subscribers`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `audit_subscribers`
  ADD FOREIGN KEY (`user_id`) REFERENCES `subscribers` (`id`);