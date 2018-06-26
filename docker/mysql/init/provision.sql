ALTER USER 'root'@'localhost' IDENTIFIED BY 'ob7apadEGAcUrJ0M4N6iDilAsRiR';

CREATE USER 'vault'@'%' IDENTIFIED BY 'vault';
GRANT ALL PRIVILEGES ON *.* TO 'vault'@'%' WITH GRANT OPTION;
GRANT CREATE USER ON *.* to 'vault'@'%';