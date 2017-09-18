SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `user_main`;
CREATE TABLE `user_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_roles` int(11) NOT NULL DEFAULT '0' COMMENT 'Úroveň registrácie a rola',
  `id_user_profiles` int(11) DEFAULT NULL COMMENT 'Užívateľský profil',
  `password` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Heslo',
  `meno` varchar(50) COLLATE utf8_bin NOT NULL COMMENT 'Meno',
  `priezvisko` varchar(50) COLLATE utf8_bin NOT NULL COMMENT 'Priezvisko',
  `email` varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'Email',
  `activated` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Aktivácia',
  `banned` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Zazázaný',
  `ban_reason` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Dôvod zákazu',
  `new_password_key` varchar(25) COLLATE utf8_bin DEFAULT NULL COMMENT 'Kľúč nového hesla',
  `new_password_requested` datetime DEFAULT NULL COMMENT 'Čas požiadavky na nové heslo',
  `new_email` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Nový email',
  `new_email_key` varchar(25) COLLATE utf8_bin DEFAULT NULL COMMENT 'Kľúč nového emailu',
  `last_ip` varchar(40) COLLATE utf8_bin DEFAULT NULL COMMENT 'Posledná IP',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Vytvorenie užívateľa',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Posledná zmena',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_registracia` (`id_user_roles`),
  KEY `id_user_profiles` (`id_user_profiles`),
  CONSTRAINT `user_main_ibfk_2` FOREIGN KEY (`id_user_profiles`) REFERENCES `user_profiles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_main_ibfk_3` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hlavné údaje užívateľa';

INSERT INTO `user_main` (`id`, `id_user_roles`, `id_user_profiles`, `password`, `meno`, `priezvisko`, `email`, `activated`, `banned`, `ban_reason`, `new_password_key`, `new_password_requested`, `new_email`, `new_email_key`, `last_ip`, `created`, `modified`) VALUES
(1,	5,	1,	'$2y$10$RnzAjUCyc/B1GgiJ9k43/e27BDz5j1vsbN.DYlfnXIxweBvqxkABq',	'Peter',	'Vojtech',	'petak23@gmail.com',	1,	0,	NULL,	NULL,	NULL,	NULL,	NULL,	'::1',	'2017-05-15 09:11:19',	'2017-05-15 10:43:01'),
(2,	4,	2,	'$2y$10$xHr8SFTodJJUqNL3SIz52uATlRdRXA2zMelzkknjWpzWTRGOQuk26',	'Róbert',	'Dula',	'lesypp@stonline.sk',	1,	0,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2017-05-15 09:13:38',	'2017-05-15 08:10:58'),
(3,	4,	3,	'$2y$10$VOeK4y3ozjaUM1aMtiVmcuHRmtcmoVvC6J4yFX4j0LZoNbXlejyMi',	'Jozef',	'Petrenčík',	'jozue@anigraph.eu',	1,	0,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2017-05-15 09:12:22',	'2017-05-15 08:10:58');

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rok` int(11) DEFAULT NULL COMMENT 'Rok narodenia',
  `telefon` varchar(30) COLLATE utf8_bin DEFAULT NULL COMMENT 'Telefón',
  `poznamka` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Poznámka',
  `pocet_pr` int(11) NOT NULL DEFAULT '0' COMMENT 'Počet prihlásení',
  `pohl` enum('Z','M') COLLATE utf8_bin NOT NULL DEFAULT 'M' COMMENT 'Pohlavie',
  `prihlas_teraz` datetime DEFAULT NULL COMMENT 'Posledné prihlásenie',
  `avatar` varchar(200) COLLATE utf8_bin DEFAULT NULL COMMENT 'Cesta k avatarovi',
  `news` enum('A','N') COLLATE utf8_bin NOT NULL DEFAULT 'A' COMMENT 'Posielanie info emailou',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `user_profiles` (`id`, `rok`, `telefon`, `poznamka`, `pocet_pr`, `pohl`, `prihlas_teraz`, `avatar`, `news`) VALUES
(1,	NULL,	NULL,	NULL,	0,	'M',	NULL,	NULL,	'A'),
(2,	NULL,	NULL,	NULL,	0,	'M',	NULL,	NULL,	'A'),
(3,	NULL,	NULL,	NULL,	0,	'M',	NULL,	NULL,	'A');

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL COMMENT '[A]Index',
  `role` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'guest' COMMENT 'Rola pre ACL',
  `inherited` varchar(30) COLLATE utf8_bin DEFAULT NULL COMMENT 'Dedí od roli',
  `name` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'Registracia cez web' COMMENT 'Názov úrovne registrácie',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Úrovne registrácie a ich názvy';

DROP TABLE IF EXISTS `user_resource`;
CREATE TABLE `user_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `name` varchar(30) COLLATE utf8_bin NOT NULL COMMENT 'Názov zdroja',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Zdroje oprávnení';

DROP TABLE IF EXISTS `user_permission`;
CREATE TABLE `user_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_roles` int(11) NOT NULL DEFAULT '0' COMMENT 'Užívateľská rola',
  `id_user_resource` int(11) NOT NULL COMMENT 'Zdroj',
  `actions` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT 'Povolenie na akciu. (Ak viac oddelené čiarkou, ak null tak všetko)',
  PRIMARY KEY (`id`),
  KEY `id_user_roles` (`id_user_roles`),
  KEY `id_user_resource` (`id_user_resource`),
  CONSTRAINT `user_permission_ibfk_1` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`),
  CONSTRAINT `user_permission_ibfk_2` FOREIGN KEY (`id_user_resource`) REFERENCES `user_resource` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Užívateľské oprávnenia';

INSERT INTO `user_roles` (`id`, `role`, `inherited`, `name`) VALUES
(0,	'guest',	NULL,	'Bez registrácie'),
(1,	'register',	'guest',	'Registrácia cez web'),
(2,	'passive',	'register',	'Pasívny užívateľ'),
(3,	'active',	'passive',	'Aktívny užívateľ'),
(4,	'manager',	'active',	'Správca obsahu'),
(5,	'admin',	'manager',	'Administrátor');

INSERT INTO `user_resource` (`id`, `name`) VALUES
(1, 'Front:Homepage'),
(2, 'Front:User'),
(3, 'Front:UserLog'),
(4, 'Front:Dokumenty'),
(5, 'Front:Error'),
(6, 'Front:Oznam'),
(7, 'Front:Clanky'),
(8, 'Front:Menu'),
(9, 'Front:Faktury'),
(10, 'Admin:Homepage'),
(11, 'Admin:User'),
(12, 'Admin:Verzie'),
(13, 'Admin:Menu'),
(14, 'Admin:Udaje'),
(15, 'Admin:Dokumenty'),
(16, 'Admin:Lang'),
(17, 'Admin:Slider'),
(18, 'Admin:Oznam'),
(19, 'Admin:Clanky'),
(20, 'Admin:Texyla');

INSERT INTO `user_permission` (`id_user_roles`, `id_user_resource`, `actions`) VALUES
(0, 4, NULL),
(0, 6, NULL), 
(0, 7, NULL),
(0, 8, NULL),
(0, 1, NULL),
(0, 5, NULL),
(0, 2, NULL),
(0, 9, 'default'),
(0, 3, 'activateNewEmail'),
(1, 3, 'default,mailChange,passwordChange'),
(3, 19, 'default,edit,edit2,add,add2'),
(3, 13, 'default,edit,edit2,add,add2'),
(3, 10, NULL),
(3, 15, NULL),
(4, 9, NULL),
(4, 19, 'addpol'), 
(4, 13, 'addpol'),
(4, 12, 'default'),
(4, 11, 'default'),
(4, 14, 'default,edit'),
(4, 18, NULL),
(4, 17, NULL),
(4, 20, NULL),
(4, 16, 'default,edit'),
(5, 16, NULL),
(5, 14, NULL),
(5, 11, NULL),
(5, 12, NULL),
(5, 13, NULL);

ALTER TABLE `dokumenty`
DROP FOREIGN KEY `dokumenty_ibfk_2`,
CHANGE `id_user_profiles` `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Id užívateľa' AFTER `id_hlavne_menu`,
ADD FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`),
COMMENT='Prílohy k článkom';

ALTER TABLE `dokumenty`
CHANGE `id_registracia` `id_user_roles` int(11) NOT NULL DEFAULT '0' COMMENT 'Id min úrovne registrácie pre zobrazenie' AFTER `id_user_main`,
ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

ALTER TABLE `verzie`
DROP FOREIGN KEY `verzie_ibfk_1`,
CHANGE `id_user_profiles` `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Id užívateľa' AFTER `id`,
CHANGE `cislo` `cislo` varchar(10) COLLATE 'utf8_bin' NOT NULL DEFAULT '' COMMENT 'Číslo verzie' AFTER `id_user_main`,
CHANGE `subory` `subory` varchar(255) COLLATE 'utf8_bin' NULL COMMENT 'Zmenené súbory' AFTER `cislo`,
CHANGE `text` `text` text COLLATE 'utf8_bin' NULL COMMENT 'Popis zmien' AFTER `subory`,
CHANGE `datum` `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum a čas zmeny' AFTER `text`,
COMMENT='Verzie webu';

DROP TABLE IF EXISTS `user_prihlasenie`;
CREATE TABLE `user_prihlasenie` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_main` int(11) NOT NULL COMMENT 'Id užívateľa',
  `log_in_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Dátum a čas prihlásenia',
  PRIMARY KEY (`id`),
  KEY `id_user_profiles` (`id_user_main`),
  CONSTRAINT `user_prihlasenie_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Evidencia prihlásenia užívateľov';

ALTER TABLE `verzie`
ADD FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`);

UPDATE `hlavne_menu` SET
`id_user_profiles` = '1',
`modified` = now();

ALTER TABLE `hlavne_menu`
DROP FOREIGN KEY `hlavne_menu_ibfk_7`,
CHANGE `id_user_profiles` `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Id užívateľa' AFTER `id_nadradenej`,
ADD FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`);

ALTER TABLE `hlavne_menu`
DROP FOREIGN KEY `hlavne_menu_ibfk_1`,
CHANGE `id_registracia` `id_user_roles` int(11) NOT NULL DEFAULT '0' COMMENT 'Id min úrovne registrácie pre zobrazenie' AFTER `id_hlavne_menu_cast`,
ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

ALTER TABLE `oznam`
DROP FOREIGN KEY `oznam_ibfk_1`,
CHANGE `id_user_profiles` `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Id užívateľa' AFTER `id`,
ADD FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`);

ALTER TABLE `oznam`
DROP FOREIGN KEY `oznam_ibfk_2`,
CHANGE `id_registracia` `id_user_roles` int(11) NOT NULL DEFAULT '0' COMMENT 'Id min úrovne registrácie pre zobrazenie' AFTER `id_user_main`,
ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

ALTER TABLE `admin_menu`
DROP FOREIGN KEY `admin_menu_ibfk_1`,
CHANGE `id_registracia` `id_user_roles` int(11) NOT NULL DEFAULT '4' COMMENT 'Id min úrovne registrácie' AFTER `nazov`,
ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

ALTER TABLE `faktury`
DROP FOREIGN KEY `faktury_ibfk_2`,
CHANGE `id_user_profiles` `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Kto pridal dokument' AFTER `subor`,
ADD FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`);

ALTER TABLE `fotky`
CHANGE `id_foto` `id` int(11) NOT NULL COMMENT '[A]Index' AUTO_INCREMENT FIRST,
CHANGE `id_clena` `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Id užívateľa' AFTER `id`,
CHANGE `id_galery` `id_galery` int(11) NOT NULL DEFAULT '0' COMMENT 'Identifikácia príslušnosti ku galérii' AFTER `id_user_main`,
CHANGE `nazov` `nazov` varchar(70) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '.jpg' COMMENT 'Názov súboru obrázku' AFTER `id_galery`,
CHANGE `pocitadlo` `pocitadlo` int(11) NOT NULL DEFAULT '0' COMMENT 'Počet zobrazení obrázku' AFTER `nazov`,
ADD FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`),
COMMENT='Obrázky vo fotogalérii' ENGINE='InnoDB';

ALTER TABLE `hlavne_menu_cast`
DROP FOREIGN KEY `hlavne_menu_cast_ibfk_1`,
CHANGE `id_registracia` `id_user_roles` int(11) NOT NULL DEFAULT '5' COMMENT 'Id min úrovne registrácie pre editáciu' AFTER `nazov`,
ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

ALTER TABLE `udaje`
DROP FOREIGN KEY `udaje_ibfk_1`,
CHANGE `id_registracia` `id_user_roles` int(11) NOT NULL DEFAULT '5' COMMENT 'Id min úrovne pre editáciu' AFTER `id`,
ADD FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`);

SET foreign_key_checks = 0;
DROP TABLE `registracia`, `users`;

ALTER TABLE `slider`
CHANGE `popis` `popis` varchar(150) COLLATE 'utf8_bin' NULL COMMENT 'Popis obrázku slideru vypisovaný v dolnej časti' AFTER `nadpis`;

UPDATE `slider` SET
`nadpis` = NULL,
`popis` = NULL,
`zobrazenie` = NULL;

DROP TABLE IF EXISTS `hlavne_menu_opravnenie`;
CREATE TABLE `hlavne_menu_opravnenie` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `nazov` varchar(40) COLLATE utf8_bin NOT NULL COMMENT 'Názov oprávnenia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Oprávnenia nevlastníkov položiek hlavného menu';

INSERT INTO `hlavne_menu_opravnenie` (`id`, `nazov`) VALUES
(0,	'Žiadne'),
(1,	'Pridávanie podčlánkov'),
(2,	'Editácia položky'),
(3,	'Pridávanie podčlánkov a editácia položky');

ALTER TABLE `hlavne_menu`
CHANGE `povol_pridanie` `id_hlavne_menu_opravnenie` int(11) NOT NULL DEFAULT '0' COMMENT 'Povolenie pre nevlastníkov (0-žiadne,1- podčlánky,2-editacia,4-všetko)' AFTER `id_hlavicka`,
ADD FOREIGN KEY (`id_hlavne_menu_opravnenie`) REFERENCES `hlavne_menu_opravnenie` (`id`),
COMMENT='Položky hlavného menu';

UPDATE `admin_menu` SET `nazov` = 'Editácia užívateľov' WHERE `id` = '4';

ALTER TABLE `oznam`
ADD `oznam_kluc` varchar(10) COLLATE 'utf8_general_ci' NULL COMMENT 'Kľúč pre potvrdenie účasti',
ADD `title_image` varchar(200) COLLATE 'utf8_general_ci' NULL COMMENT 'Názov titulného obrázku' AFTER `oznam_kluc`,
ADD `title_image_url` varchar(200) COLLATE 'utf8_general_ci' NULL COMMENT 'Odkaz titulného obrázka' AFTER `title_image`,
ADD `title_fa_class` varchar(20) COLLATE 'utf8_general_ci' NULL COMMENT 'Názov ikonky pre font awesome' AFTER `title_image`,
CHANGE `id_ikonka` `id_ikonka` int(11) NULL COMMENT 'Id použitej ikonky' AFTER `id_user_roles`,
COMMENT='Oznamy';

UPDATE `oznam` SET
`id` = '5',
`id_user_main` = '1',
`id_user_roles` = '0',
`id_ikonka` = NULL,
`datum_platnosti` = '2017-06-21',
`datum_zadania` = '2017-06-21',
`nazov` = 'Súťaž - Objav Mestské lesy Poprad',
`text` = '<p class=\"card-block text-justify\"> Objav prírodné prostredie mestských lesov, krásu a rozmanitosť prírody a histórie. Zaznamenaj rôznou formou zaujímavosti územia, pomôž k vytvoreniu nových poznatkov o území a vyhraj zaujímavé ceny... </p>\r\n<p>\r\n	{end}</p>\r\n<p class=\"card-block text-justify\"> Súťaž je určená žiakom škôl na území mesta Poprad a jeho občanov, ktorí sa do súťaže zaregistrujú na stránke sutaz.lesypoprad.sk. Súťaž potrvá v období od od 7.4. - do 30. 9 2017. Súťaže sa môžu zúčastniť aj tímy žiakov z rovnakej školy, ktorí sa do súťaže prihlásia spoločne. Web súťaže: \r\n          </p>\r\n          <strong><a href=\"http://sutaz.lesypoprad.sk\" target=\"new\">sutaz.lesypoprad.sk</a></strong>\r\n',
`oznam_kluc` = 'ltqdqvhjno',
`title_image` = 'snezienka.jpg',
`title_fa_class` = NULL,
`title_image_url` = 'http://sutaz.lesypoprad.sk/'
WHERE `id` = '5';

ALTER TABLE `hlavne_menu_lang`
CHANGE `nazov` `menu_name` varchar(100) COLLATE 'utf8_bin' NULL COMMENT 'Názov položky v hlavnom menu pre daný jazyk' AFTER `id_clanok_lang`,
CHANGE `description` `view_name` varchar(255) COLLATE 'utf8_bin' NULL COMMENT 'Zobrazený názov položky pre daný jazyk' AFTER `h1part2`;
-- ---------------------------------------------------------------------------------------------------------------------------------------
-- DROP TABLE IF EXISTS `debata`;
-- CREATE TABLE `debata` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
--   `id_user_main` int(11) NOT NULL DEFAULT '1' COMMENT 'Id užívateľa',
--   `text` text COLLATE utf8_bin NOT NULL COMMENT 'Text príspevku',
--   `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Časová pečiatka uloženia príspevku',
--   PRIMARY KEY (`id`),
--   KEY `kedy` (`timestamp`),
--   KEY `id_user_profiles` (`id_user_main`),
--   CONSTRAINT `debata_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- DROP TABLE IF EXISTS `oznam_komentar`;
-- CREATE TABLE `oznam_komentar` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
--   `id_oznam` int(11) NOT NULL COMMENT 'Id oznamu, ku ktorému patrí',
--   `id_user_main` int(11) NOT NULL COMMENT 'Id užívateľa',
--   `text` text COLLATE utf8_bin NOT NULL COMMENT 'Komentár',
--   `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Časová značka',
--   PRIMARY KEY (`id`),
--   KEY `id_oznam` (`id_oznam`),
--   KEY `id_user_profiles` (`id_user_main`),
--   CONSTRAINT `oznam_komentar_ibfk_1` FOREIGN KEY (`id_oznam`) REFERENCES `oznam` (`id`),
--   CONSTRAINT `oznam_komentar_ibfk_2` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Komentáre k oznamom';

-- DROP TABLE IF EXISTS `clanok_komentar`;
-- CREATE TABLE `clanok_komentar` (
--   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
--   `id_hlavne_menu` int(11) NOT NULL COMMENT 'Id hl. menu, ku ktorému patrí',
--   `id_user_main` int(11) NOT NULL COMMENT 'Id užívateľa',
--   `text` text COLLATE utf8_bin NOT NULL COMMENT 'Komentár',
--   `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum vytvorenia alebo zmeny',
--   PRIMARY KEY (`id`),
--   KEY `id_hlavne_menu` (`id_hlavne_menu`),
--   KEY `id_user_profiles` (`id_user_main`),
--   CONSTRAINT `clanok_komentar_ibfk_1` FOREIGN KEY (`id_hlavne_menu`) REFERENCES `hlavne_menu` (`id`),
--   CONSTRAINT `clanok_komentar_ibfk_2` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Komentáre k článokom';
