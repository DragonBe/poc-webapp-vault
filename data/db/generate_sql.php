<?php

echo 'DROP TABLE IF EXISTS `eu_country`' . PHP_EOL;
echo 'CREATE TABLE `eu_country` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `country` VARCHAR(60) NOT NULL DEFAULT "", `code` CHAR(2) NOT NULL DEFAULT "",`flag` VARCHAR(60) NOT NULL DEFAULT "", PRIMARY KEY (`id`) ) Engine=InnoDB CHARSET utf8 COLLATE utf8_general_ci;' . PHP_EOL;
$data = require_once __DIR__ . '/eu.php';
foreach ($data as $entry) {
    echo sprintf(
        'INSERT INTO `eu_country` (`country`, `code`, `flag`) VALUES ("%s", "%s", "%s");',
        $entry['country'],
        $entry['code'],
        $entry['flag']
    ) . PHP_EOL;
}
