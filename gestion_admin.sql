CREATE DATABASE gestion_admin CHARACTER SET utf8mb4;
USE gestion_admin;

CREATE TABLE administrateur (
	ID_admin VARCHAR(50) NOT NULL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    Prénom VARCHAR(50) NOT NULL,
    Email  VARCHAR(50) NOT NULL
);

show tables;

select * from administrateur;

INSERT INTO administrateur values
	('AD_1','Master','King','bado@ang.gmail','admin_pw');

ALTER TABLE administrateur
ADD COLUMN mdp varchar(50) NOT NULL;

UPDATE  administrateur
SET mdp = 'admin1_pw'
WHERE ID_admin='AD_1';
