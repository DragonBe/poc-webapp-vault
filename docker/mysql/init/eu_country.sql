DROP DATABASE IF EXISTS `europe`;
CREATE DATABASE `europe`;

DROP TABLE IF EXISTS `europe`.`eu_country`;
CREATE TABLE `europe`.`eu_country` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, 
  `country` VARCHAR(60) NOT NULL DEFAULT "", 
  `code` CHAR(2) NOT NULL DEFAULT "",
  `flag` VARCHAR(60) NOT NULL DEFAULT "", 
  PRIMARY KEY (`id`) 
) Engine=InnoDB CHARSET utf8 COLLATE utf8_general_ci;

INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Austria", "AU", "austria");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Belgium", "BE", "belgium");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Bulgaria", "BG", "bulgaria");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Croatia", "HR", "croatia");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Cyprus", "CY", "cyprus");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Czech Republic", "CZ", "czech-republic");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Denmark", "DK", "denmark");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Estonia", "EE", "estonia");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Finland", "FI", "finland");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("France", "FR", "france");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Germany", "DE", "germany");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Greece", "GR", "greece");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Hungary", "HU", "hungary");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Ireland", "IE", "ireland");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Italy", "IT", "italy");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Latvia", "LV", "latvian");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Lithuania", "LT", "lithuania");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Luxembourg", "LU", "luxembourg");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Malta", "MT", "malta");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Netherlands", "NL", "netherlands");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Poland", "PO", "poland");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Portugal", "PT", "portugal");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Romania", "RO", "romania");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Slovakia", "SK", "slovakia");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Slovenia", "SI", "slovenia");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Spain", "ES", "spain");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("Sweden", "SE", "sweden");
INSERT INTO `europe`.`eu_country` (`country`, `code`, `flag`) VALUES ("United Kingdom", "UK", "uk");
