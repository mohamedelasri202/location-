-- Création de la base de données
CREATE DATABASE location;
USE location ;

-- Table: Role
CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- Table: Utilisateur
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE
);

-- Table: Categorie
CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Table: Vehicule
CREATE TABLE vehicule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    categorie_id INT NOT NULL,
    prix_journalier DECIMAL(10, 2) NOT NULL,
    disponibilite BOOLEAN DEFAULT TRUE,
    description TEXT,
    image_url VARCHAR(255),
    FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE CASCADE
);

-- Table: Reservation
CREATE TABLE reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    vehicule_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    lieu_prise_en_charge VARCHAR(255) NOT NULL,
    statut ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicule_id) REFERENCES vehicule(id) ON DELETE CASCADE
);

-- Table: Avis
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    vehicule_id INT NOT NULL,
    commentaire TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    soft_deleted BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicule_id) REFERENCES vehicule(id) ON DELETE CASCADE
);
*********************************************
-- Article Table
CREATE TABLE Article (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    estApprouve BOOLEAN DEFAULT FALSE,
    statut VARCHAR(50),
    utilisateur_id INT NOT NULL,
    theme_id INT NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES User(id),
    FOREIGN KEY (theme_id) REFERENCES Theme(id)
);

-- Theme Table
CREATE TABLE Theme (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Tag Table
CREATE TABLE Tag (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- Article_Tag Table (Junction table for Many-to-Many relationship)
CREATE TABLE Article_Tag (
    article_id INT,
    tag_id INT,
    PRIMARY KEY (article_id, tag_id),
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES Tag(id) ON DELETE CASCADE
);

-- Commentaire Table
CREATE TABLE Commentaire (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contenu TEXT NOT NULL,
    dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT NOT NULL,
    article_id INT NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES User(id),
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE
);

-- Favori Table
CREATE TABLE Favori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dateAjout DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilisateur_id INT NOT NULL,
    article_id INT NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES User(id),
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (utilisateur_id, article_id)
);