CREATE DATABASE IF NOT EXISTS 'sensor_arduino' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE sensor_arduino;

CREATE TABLE sensores (
    id int(11) NOT NULL AUTO_INCREMENT,
    amper varchar(100) NOT NULL,
    watts varchar(100) NOT NULL,
    kwh varchar(250) NOT NULL,
    data date NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB COLLATE=utf8_general_ci;