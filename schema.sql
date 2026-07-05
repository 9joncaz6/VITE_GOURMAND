-- ============================================================
-- 1) Suppression des tables (ordre sécurisé pour les relations)
-- ============================================================

DROP TABLE IF EXISTS commande_statut;
DROP TABLE IF EXISTS commande_item;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS theme;
DROP TABLE IF EXISTS regime;
DROP TABLE IF EXISTS utilisateur;

-- ============================================================
-- 2) Table UTILISATEUR
-- ============================================================

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    gsm VARCHAR(180) NULL,
    adresse_postale TEXT NULL,
    password VARCHAR(255) NOT NULL,
    roles JSON NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    reset_token VARCHAR(100) NULL
) ENGINE=InnoDB;

-- ============================================================
-- 3) Table THEME
-- ============================================================

CREATE TABLE theme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4) Table REGIME
-- ============================================================

CREATE TABLE regime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ============================================================
-- 5) Table PLAT
-- ============================================================

CREATE TABLE plat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT NULL,
    type VARCHAR(20) NOT NULL,
    image VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ============================================================
-- 6) Table MENU
-- ============================================================

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    nb_personnes_min INT NOT NULL,
    prix_base FLOAT NOT NULL,
    stock_disponible INT NOT NULL,
    theme_id INT NULL,
    regime_id INT NULL,
    images JSON NOT NULL,
    image VARCHAR(255) NULL,

    CONSTRAINT fk_menu_theme FOREIGN KEY (theme_id)
        REFERENCES theme(id) ON DELETE SET NULL,

    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id)
        REFERENCES regime(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 7) Table MENU_PLAT (relation ManyToMany)
-- ============================================================

CREATE TABLE menu_plat (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,

    PRIMARY KEY(menu_id, plat_id),

    CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id)
        REFERENCES menu(id) ON DELETE CASCADE,

    CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id)
        REFERENCES plat(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8) Table COMMANDE
-- ============================================================

CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'en_attente',
    total FLOAT NOT NULL,
    utilisateur_id INT NOT NULL,
    frais_livraison FLOAT NOT NULL,

    CONSTRAINT fk_commande_user FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9) Table COMMANDE_ITEM
-- ============================================================

CREATE TABLE commande_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire FLOAT NOT NULL,

    CONSTRAINT fk_ci_commande FOREIGN KEY (commande_id)
        REFERENCES commande(id) ON DELETE CASCADE,

    CONSTRAINT fk_ci_menu FOREIGN KEY (menu_id)
        REFERENCES menu(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 10) Table AVIS
-- ============================================================

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note INT NOT NULL,
    commentaire TEXT NOT NULL,
    date DATETIME NOT NULL,
    menu_id INT NOT NULL,
    commande_id INT NOT NULL UNIQUE,
    utilisateur_id INT NOT NULL,
    valide BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT fk_avis_menu FOREIGN KEY (menu_id)
        REFERENCES menu(id) ON DELETE CASCADE,

    CONSTRAINT fk_avis_commande FOREIGN KEY (commande_id)
        REFERENCES commande(id) ON DELETE CASCADE,

    CONSTRAINT fk_avis_user FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11) Table COMMANDE_STATUT (historique)
-- ============================================================

CREATE TABLE commande_statut (
    id INT AUTO_INCREMENT PRIMARY KEY,
    statut VARCHAR(50) NOT NULL,
    date_maj DATETIME NOT NULL,
    commentaire TEXT NULL,
    commande_id INT NOT NULL,

    CONSTRAINT fk_cs_commande FOREIGN KEY (commande_id)
        REFERENCES commande(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- FIN DU SCHEMA
-- ============================================================

