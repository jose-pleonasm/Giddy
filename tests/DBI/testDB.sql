-- ---------------------------------------------------------
-- SQL skript pro vytvoreni DB pro test DBI
--
-- Pro MySQL verze 4.1.0 a vyssi
--
-- vytvoreno: 23.9.2008 16:45
-- posledni zmena: 23.9.2008 16:45
-- ----------------------------------------------------------

-- nastaveni kodovani
SET NAMES 'utf8';

-- if[db not exist] vytvoreni db
CREATE DATABASE _test DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;
USE _test;

--
-- Uzivatele systemu
--

CREATE TABLE t_data (
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  txt VARCHAR(20) NOT NULL,
  flag TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id)
) TYPE=InnoDB COMMENT='Zaznamy'
  DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci;


INSERT INTO t_data (txt, flag, updated) values ('row 1', 1, NOW());
INSERT INTO t_data (txt, flag, updated) values ('row 2', 0, NOW());
INSERT INTO t_data (txt, flag, updated) values ('row 3', 0, NOW());
INSERT INTO t_data (txt, flag, updated) values ('row 4', 0, NOW());
