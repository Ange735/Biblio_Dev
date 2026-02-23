CREATE DATABASE gestion_bibliothèque CHARACTER SET utf8mb4;
USE gestion_bibliothèque;

CREATE TABLE étudiant (
	ID_etu VARCHAR(50) NOT NULL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    Prénom VARCHAR(50) NOT NULL,
    Email  VARCHAR(50) NOT NULL,
    nbr_retard  INT(5) NOT NULL,
    statue_etu VARCHAR(50) NOT NULL
);

CREATE TABLE categorie (
	ID_cat VARCHAR(50) NOT NULL  PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

CREATE TABLE livre (
	ISBN INT(6) NOT NULL PRIMARY KEY,
    titre VARCHAR(50) NOT NULL,
    auteur VARCHAR(50) NOT NULL,
    ID_cat VARCHAR(50) NOT NULL,
    année_par  DATE NOT NULL,
    nbr_exemp  INT(10) NOT NULL,
    nbr_empr  INT(10) NOT NULL,
    statue_liv VARCHAR(50) NOT NULL,
    note NUMERIC(10),
    imag VARCHAR(50) NOT NULL,
    FOREIGN KEY(ID_cat) REFERENCES categorie(ID_cat)
		ON DELETE CASCADE
        ON UPDATE CASCADE
);
Create table evaluation(
id INT(10) auto_increment,
ISBN INT(6)  NOT NULL,
ID_etu VARCHAR(50) NOT NULL,
vote_tot numeric(10) NOT NULL,
primary key(id),
FOREIGN KEY(ID_etu) REFERENCES étudiant(ID_etu)
		ON DELETE CASCADE
        ON UPDATE CASCADE,
	FOREIGN KEY(ISBN) REFERENCES livre(ISBN)
		ON DELETE CASCADE
        ON UPDATE CASCADE) ;

alter table livre
drop column nbr_vote,
drop column vote_tot;

drop table evaluation;


CREATE TABLE emprunt (
	ISBN INT(6) NOT NULL ,
    ID_etu VARCHAR(50) NOT NULL,
    date_reserv DATE NOT NULL,
    date_empr DATE NOT NULL,
    date_retour DATE NOT NULL,
    date_retour_eff DATE NOT NULL,
    statue_empr VARCHAR(50) NOT NULL,
	PRIMARY KEY(ID_etu,ISBN,date_reserv),
    FOREIGN KEY(ID_etu) REFERENCES étudiant(ID_etu)
		ON DELETE CASCADE
        ON UPDATE CASCADE,
	FOREIGN KEY(ISBN) REFERENCES livre(ISBN)
		ON DELETE CASCADE
        ON UPDATE CASCADE
);

alter table emprunt
modify date_retour_eff DATE;

CREATE TABLE liste_att (
    numero INT(6) NOT NULL AUTO_INCREMENT,
    ISBN INT(6) NOT NULL,
    ID_etu VARCHAR(50) NOT NULL,
    PRIMARY KEY (numero),
    FOREIGN KEY (ID_etu) REFERENCES étudiant(ID_etu)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (ISBN) REFERENCES livre(ISBN)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
ALTER TABLE étudiant
ADD COLUMN mdp varchar(50) NOT NULL;

DROP DATABASE gestion_bibliothèque;

DROP TABLE categorie;
show tables;


show tables;
select * from categorie;
select * from étudiant;
select * from livre;
select * from reservation;
select * from emprunt;
select * from liste_att;
select * from message_etu;
select * from message_admin;
select * from evaluation;

TRUNCATE TABLE message_etu;
alter table message_etu
add column id INT auto_increment;
drop table message_etu;

update emprunt
set date_retour='2025-12-24'
where ISBN IN (3,2);

INSERT INTO categorie values
	('CAT_1','Maths'),
    ('CAT_2','littérature'),
    ('CAT_3','Physique'),
    ('CAT_4','Sport'),
    ('CAT_5','Programmation');


CREATE TABLE reservation (
    id_reservation INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ISBN INT(6) NOT NULL,
    ID_etu VARCHAR(50) NOT NULL,
    date_reserv DATE NOT NULL DEFAULT (CURRENT_DATE),
    statut ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    FOREIGN KEY (ISBN) REFERENCES livre(ISBN)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (ID_etu) REFERENCES étudiant(ID_etu)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE message_admin (
id_etudiant VARCHAR(50) NOT NULL,
message VARCHAR(250) NOT NULL
);

CREATE TABLE message_etu(
id INT auto_increment,
mess VARCHAR(250),
id_etu VARCHAR(250),
primary key(id)
);

alter table message_admin
add column messag VARCHAR(250);
