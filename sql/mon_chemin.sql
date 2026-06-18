-- =====================================================
-- BASE DE DONNÉES : Mon Chemin
-- Version : 3.0 - Structure simplifiée et complète
-- =====================================================

-- Supprimer la base si elle existe (optionnel)
-- DROP DATABASE IF EXISTS mon_chemin;

CREATE DATABASE IF NOT EXISTS mon_chemin 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE mon_chemin;

-- =====================================================
-- TABLE : utilisateurs
-- =====================================================
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(30),
    niveau VARCHAR(50),
    situation_handicap TINYINT(1) DEFAULT 0,
    type_handicap VARCHAR(100),
    parent_email VARCHAR(100),
    parent_whatsapp VARCHAR(20),
    role ENUM('eleve','admin') DEFAULT 'eleve',
    actif TINYINT(1) DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME
);

-- =====================================================
-- TABLE : domaines (10 domaines)
-- =====================================================
CREATE TABLE domaines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    icone VARCHAR(50),
    actif TINYINT(1) DEFAULT 1
);

-- =====================================================
-- TABLE : filieres (par domaine)
-- =====================================================
CREATE TABLE filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domaine_id INT NOT NULL,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    duree_etudes VARCHAR(100),
    cout_moyen VARCHAR(100),
    actif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (domaine_id) REFERENCES domaines(id)
);

-- =====================================================
-- TABLE : universites (Afrique de l'Ouest)
-- =====================================================
CREATE TABLE universites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    pays VARCHAR(100),
    ville VARCHAR(100),
    type_universite ENUM('publique','privee') DEFAULT 'publique',
    site_web VARCHAR(255),
    description TEXT,
    logo VARCHAR(255),
    actif TINYINT(1) DEFAULT 1
);

-- =====================================================
-- TABLE : universites_filieres (liaison)
-- =====================================================
CREATE TABLE universites_filieres (
    universite_id INT NOT NULL,
    filiere_id INT NOT NULL,
    PRIMARY KEY(universite_id, filiere_id),
    FOREIGN KEY (universite_id) REFERENCES universites(id) ON DELETE CASCADE,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE : quiz_questions (22 questions)
-- =====================================================
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    question_text TEXT NOT NULL,
    type_reponse ENUM('radio','carte') DEFAULT 'radio',
    est_plaisir TINYINT(1) DEFAULT 0,
    ordre INT NOT NULL,
    actif TINYINT(1) DEFAULT 1
);

-- =====================================================
-- TABLE : quiz_options (avec scores JSON)
-- =====================================================
CREATE TABLE quiz_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_valeur VARCHAR(100) NOT NULL,
    option_texte VARCHAR(255) NOT NULL,
    option_icone VARCHAR(100),
    scores_domaines JSON NOT NULL,
    ordre INT DEFAULT 1,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE : quiz_sessions (session de quiz)
-- =====================================================
CREATE TABLE quiz_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_fin TIMESTAMP NULL,
    statut ENUM('en_cours', 'termine', 'abandonne') DEFAULT 'en_cours',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- =====================================================
-- TABLE : quiz_reponses (réponses détaillées)
-- =====================================================
CREATE TABLE quiz_reponses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    question_id INT NOT NULL,
    option_id INT NOT NULL,
    date_reponse TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id),
    FOREIGN KEY (option_id) REFERENCES quiz_options(id)
);

-- =====================================================
-- TABLE : quiz_resultats (résultats calculés)
-- =====================================================
CREATE TABLE quiz_resultats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    scores_domaines JSON,
    domaines_principaux JSON,
    filieres_proposees JSON,
    universites_proposees JSON,
    statut ENUM('en_attente', 'valide', 'modifie', 'publie', 'rejete') DEFAULT 'en_attente',
    commentaire_admin TEXT,
    admin_id INT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation TIMESTAMP NULL,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (admin_id) REFERENCES utilisateurs(id)
);

-- =====================================================
-- TABLE : validations_admin (historique)
-- =====================================================
CREATE TABLE validations_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resultat_id INT NOT NULL,
    admin_id INT NOT NULL,
    action VARCHAR(100),
    filieres_avant JSON,
    filieres_apres JSON,
    commentaire TEXT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resultat_id) REFERENCES quiz_resultats(id),
    FOREIGN KEY (admin_id) REFERENCES utilisateurs(id)
);

-- =====================================================
-- TABLE : documents_pdf
-- =====================================================
CREATE TABLE documents_pdf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resultat_id INT NOT NULL,
    chemin_fichier VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    envoye_email TINYINT(1) DEFAULT 0,
    envoye_whatsapp TINYINT(1) DEFAULT 0,
    FOREIGN KEY (resultat_id) REFERENCES quiz_resultats(id)
);

-- =====================================================
-- TABLE : notifications
-- =====================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(255),
    message TEXT,
    lu TINYINT(1) DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- =====================================================
-- TABLE : logs (RGPD)
-- =====================================================
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NULL,
    action VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- INSERTION DES DONNÉES
-- =====================================================

-- Admin par défaut
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Système', 'admin@monchemin.com', 'admin123', 'admin');

-- =====================================================
-- 1. DOMAINES (10 domaines)
-- =====================================================
INSERT INTO domaines (nom, description, icone) VALUES
('Informatique & Numérique', 'Développement logiciel, cybersécurité, intelligence artificielle, réseaux et technologies numériques.', 'fa-microchip'),
('Santé', 'Médecine, pharmacie, soins infirmiers et professions de santé.', 'fa-heart-pulse'),
('Génie Civil & BTP', 'Construction, infrastructures, architecture et travaux publics.', 'fa-building'),
('Gestion & Commerce', 'Management, comptabilité, finance, marketing et entrepreneuriat.', 'fa-chart-line'),
('Droit & Sciences Politiques', 'Droit public, droit privé, administration publique et diplomatie.', 'fa-gavel'),
('Communication & Médias', 'Journalisme, communication digitale, audiovisuel et relations publiques.', 'fa-bullhorn'),
('Agronomie & Environnement', 'Agriculture, élevage, environnement et développement durable.', 'fa-leaf'),
('Éducation & Enseignement', 'Formation des enseignants et sciences de l\'éducation.', 'fa-chalkboard-user'),
('Sciences Fondamentales', 'Mathématiques, physique, chimie, biologie et recherche.', 'fa-flask'),
('Arts, Design & Création', 'Design, arts visuels, mode, cinéma et création numérique.', 'fa-palette');

-- =====================================================
-- 2. FILIÈRES (par domaine)
-- =====================================================

-- Informatique (domaine 1)
INSERT INTO filieres (domaine_id, nom, description, duree_etudes, cout_moyen) VALUES
(1, 'Génie Logiciel', 'Conception et développement de logiciels.', '3-5 ans', '200 000 - 500 000 FCFA'),
(1, 'Développement Web', 'Création de sites et applications web.', '2-3 ans', '150 000 - 400 000 FCFA'),
(1, 'Développement Mobile', 'Applications Android et iOS.', '2-3 ans', '150 000 - 400 000 FCFA'),
(1, 'Cybersécurité', 'Protection des systèmes informatiques.', '3-5 ans', '250 000 - 600 000 FCFA'),
(1, 'Intelligence Artificielle', 'IA, machine learning et deep learning.', '5 ans', '300 000 - 700 000 FCFA'),
(1, 'Data Science', 'Analyse et exploitation des données.', '3-5 ans', '250 000 - 600 000 FCFA'),
(1, 'Big Data', 'Gestion et analyse de grandes masses de données.', '3-5 ans', '250 000 - 600 000 FCFA'),
(1, 'Cloud Computing', 'Infrastructures et services cloud.', '3 ans', '200 000 - 500 000 FCFA'),
(1, 'Réseaux et Télécommunications', 'Administration et sécurisation des réseaux.', '2-3 ans', '150 000 - 400 000 FCFA'),
(1, 'Robotique', 'Conception de systèmes automatisés.', '5 ans', '300 000 - 700 000 FCFA'),

-- Santé (domaine 2)
(2, 'Médecine Générale', 'Soins et diagnostic des patients.', '7 ans', '500 000 - 1 500 000 FCFA'),
(2, 'Pharmacie', 'Préparation et délivrance des médicaments.', '5 ans', '400 000 - 1 000 000 FCFA'),
(2, 'Soins Infirmiers', 'Soins aux patients en milieu hospitalier.', '3 ans', '200 000 - 500 000 FCFA'),
(2, 'Sage-Femme', 'Suivi des grossesses et accouchements.', '4 ans', '250 000 - 600 000 FCFA'),
(2, 'Kinésithérapie', 'Rééducation fonctionnelle.', '4 ans', '250 000 - 600 000 FCFA'),
(2, 'Nutrition', 'Conseil en alimentation et santé.', '3 ans', '200 000 - 500 000 FCFA'),

-- Génie Civil (domaine 3)
(3, 'Génie Civil', 'Conception d\'ouvrages d\'infrastructure.', '5 ans', '300 000 - 700 000 FCFA'),
(3, 'Architecture', 'Conception de bâtiments.', '5 ans', '300 000 - 700 000 FCFA'),
(3, 'Urbanisme', 'Aménagement des espaces urbains.', '5 ans', '300 000 - 700 000 FCFA'),
(3, 'Topographie', 'Mesure et cartographie.', '3 ans', '200 000 - 500 000 FCFA'),
(3, 'Hydraulique', 'Gestion des eaux.', '5 ans', '300 000 - 700 000 FCFA'),

-- Gestion (domaine 4)
(4, 'Comptabilité', 'Gestion des comptes et bilans.', '3 ans', '150 000 - 400 000 FCFA'),
(4, 'Finance', 'Gestion des flux financiers.', '3-5 ans', '200 000 - 500 000 FCFA'),
(4, 'Marketing', 'Stratégies de vente et communication.', '3 ans', '150 000 - 400 000 FCFA'),
(4, 'Gestion des Ressources Humaines', 'Gestion du personnel.', '3 ans', '150 000 - 400 000 FCFA'),
(4, 'Management', 'Direction d\'entreprise.', '5 ans', '250 000 - 600 000 FCFA'),
(4, 'Entrepreneuriat', 'Création et gestion d\'entreprise.', '3 ans', '150 000 - 400 000 FCFA'),
(4, 'Commerce International', 'Import-export et logistique.', '3 ans', '200 000 - 500 000 FCFA'),

-- Droit (domaine 5)
(5, 'Droit des Affaires', 'Droit commercial et sociétés.', '5 ans', '200 000 - 500 000 FCFA'),
(5, 'Droit International', 'Droit et relations entre états.', '5 ans', '200 000 - 500 000 FCFA'),
(5, 'Administration Publique', 'Gestion des institutions publiques.', '3 ans', '150 000 - 400 000 FCFA'),
(5, 'Diplomatie', 'Relations internationales et diplomatie.', '5 ans', '250 000 - 600 000 FCFA'),

-- Communication (domaine 6)
(6, 'Journalisme', 'Information et reportage.', '3 ans', '150 000 - 400 000 FCFA'),
(6, 'Communication Digitale', 'Stratégies de communication en ligne.', '3 ans', '150 000 - 400 000 FCFA'),
(6, 'Marketing Digital', 'Publicité et promotion en ligne.', '3 ans', '150 000 - 400 000 FCFA'),
(6, 'Relations Publiques', 'Gestion de l\'image et des relations.', '3 ans', '150 000 - 400 000 FCFA'),
(6, 'Community Management', 'Animation des communautés en ligne.', '2 ans', '120 000 - 300 000 FCFA'),

-- Agronomie (domaine 7)
(7, 'Agronomie', 'Sciences agricoles.', '5 ans', '200 000 - 500 000 FCFA'),
(7, 'Production Végétale', 'Culture et exploitation des végétaux.', '3 ans', '150 000 - 400 000 FCFA'),
(7, 'Production Animale', 'Élevage et gestion du bétail.', '3 ans', '150 000 - 400 000 FCFA'),
(7, 'Agroalimentaire', 'Transformation des produits agricoles.', '3-5 ans', '200 000 - 500 000 FCFA'),
(7, 'Développement Rural', 'Aménagement des zones rurales.', '3 ans', '150 000 - 400 000 FCFA'),

-- Éducation (domaine 8)
(8, 'Enseignement Mathématiques', 'Enseignement des mathématiques.', '3-5 ans', '120 000 - 300 000 FCFA'),
(8, 'Enseignement Sciences', 'Enseignement des sciences.', '3-5 ans', '120 000 - 300 000 FCFA'),
(8, 'Sciences de l\'Éducation', 'Théories et pratiques pédagogiques.', '5 ans', '200 000 - 500 000 FCFA'),

-- Sciences Fondamentales (domaine 9)
(9, 'Mathématiques', 'Recherche et enseignement des maths.', '5 ans', '150 000 - 400 000 FCFA'),
(9, 'Physique', 'Recherche et enseignement de la physique.', '5 ans', '150 000 - 400 000 FCFA'),
(9, 'Chimie', 'Recherche et enseignement de la chimie.', '5 ans', '150 000 - 400 000 FCFA'),
(9, 'Biologie', 'Recherche et enseignement de la biologie.', '5 ans', '150 000 - 400 000 FCFA'),

-- Arts (domaine 10)
(10, 'Design Graphique', 'Création visuelle et graphique.', '3 ans', '150 000 - 400 000 FCFA'),
(10, 'Design UX/UI', 'Conception d\'interfaces utilisateur.', '3 ans', '200 000 - 500 000 FCFA'),
(10, 'Animation 2D/3D', 'Création d\'animations numériques.', '3 ans', '200 000 - 500 000 FCFA'),
(10, 'Photographie', 'Prise de vue et retouche photo.', '2-3 ans', '120 000 - 300 000 FCFA');

-- =====================================================
-- 3. UNIVERSITÉS (Afrique de l'Ouest)
-- =====================================================
INSERT INTO universites (nom, pays, ville, type_universite, site_web) VALUES
-- Bénin
('Université d\'Abomey-Calavi', 'Bénin', 'Abomey-Calavi', 'publique', 'https://www.uac.bj'),
('Université de Parakou', 'Bénin', 'Parakou', 'publique', 'https://www.up.bj'),
('ESGIS Bénin', 'Bénin', 'Cotonou', 'privee', 'https://www.esgis.bj'),
('HECM', 'Bénin', 'Cotonou', 'privee', 'https://www.hecm.bj'),
('Institut CERCO', 'Bénin', 'Cotonou', 'privee', 'https://www.cerco.bj'),
('ISM Adonaï', 'Bénin', 'Porto-Novo', 'privee', 'https://www.ismadonai.bj'),
('Pigier Bénin', 'Bénin', 'Cotonou', 'privee', 'https://www.pigier-benin.com'),
('EPITECH Bénin', 'Bénin', 'Cotonou', 'privee', 'https://www.epitech.bj'),

-- Togo
('Université de Lomé', 'Togo', 'Lomé', 'publique', 'https://www.univ-lome.tg'),
('Université de Kara', 'Togo', 'Kara', 'publique', 'https://www.uk.tg'),
('ESGIS Togo', 'Togo', 'Lomé', 'privee', 'https://www.esgis.tg'),

-- Ghana
('University of Ghana', 'Ghana', 'Accra', 'publique', 'https://www.ug.edu.gh'),
('KNUST', 'Ghana', 'Kumasi', 'publique', 'https://www.knust.edu.gh'),
('Ashesi University', 'Ghana', 'Accra', 'privee', 'https://www.ashesi.edu.gh'),

-- Nigeria
('University of Lagos', 'Nigeria', 'Lagos', 'publique', 'https://www.unilag.edu.ng'),
('Covenant University', 'Nigeria', 'Ota', 'privee', 'https://www.covenantuniversity.edu.ng'),
('Obafemi Awolowo University', 'Nigeria', 'Ile-Ife', 'publique', 'https://www.oauife.edu.ng'),

-- Sénégal
('Université Cheikh Anta Diop', 'Sénégal', 'Dakar', 'publique', 'https://www.ucad.sn'),
('Université Gaston Berger', 'Sénégal', 'Saint-Louis', 'publique', 'https://www.ugb.sn'),
('ISM Dakar', 'Sénégal', 'Dakar', 'privee', 'https://www.ism.sn'),

-- Côte d'Ivoire
('Université Félix Houphouët-Boigny', 'Côte d\'Ivoire', 'Abidjan', 'publique', 'https://www.univ-fhb.ci'),
('INP-HB', 'Côte d\'Ivoire', 'Yamoussoukro', 'publique', 'https://www.inphb.ci'),
('Université Nangui Abrogoua', 'Côte d\'Ivoire', 'Abidjan', 'publique', 'https://www.univ-na.ci'),

-- Burkina Faso
('Université Joseph Ki-Zerbo', 'Burkina Faso', 'Ouagadougou', 'publique', 'https://www.univ-ouaga.bf'),
('Université Nazi Boni', 'Burkina Faso', 'Bobo-Dioulasso', 'publique', 'https://www.univ-bobo.bf'),

-- Mali
('Université de Bamako', 'Mali', 'Bamako', 'publique', 'https://www.ub.edu.ml');

-- =====================================================
-- =====================================================
-- 4. LIAISON UNIVERSITÉS - FILIÈRES (CORRIGÉE)
-- =====================================================

-- UAC (id 1) : Informatique, Santé, Gestion, Droit, Communication, Sciences
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(1, (SELECT id FROM filieres WHERE nom = 'Génie Logiciel')),
(1, (SELECT id FROM filieres WHERE nom = 'Développement Web')),
(1, (SELECT id FROM filieres WHERE nom = 'Développement Mobile')),
(1, (SELECT id FROM filieres WHERE nom = 'Cybersécurité')),
(1, (SELECT id FROM filieres WHERE nom = 'Intelligence Artificielle')),
(1, (SELECT id FROM filieres WHERE nom = 'Data Science')),
(1, (SELECT id FROM filieres WHERE nom = 'Big Data')),
(1, (SELECT id FROM filieres WHERE nom = 'Cloud Computing')),
(1, (SELECT id FROM filieres WHERE nom = 'Réseaux et Télécommunications')),
(1, (SELECT id FROM filieres WHERE nom = 'Robotique')),
(1, (SELECT id FROM filieres WHERE nom = 'Médecine Générale')),
(1, (SELECT id FROM filieres WHERE nom = 'Pharmacie')),
(1, (SELECT id FROM filieres WHERE nom = 'Soins Infirmiers')),
(1, (SELECT id FROM filieres WHERE nom = 'Sage-Femme')),
(1, (SELECT id FROM filieres WHERE nom = 'Kinésithérapie')),
(1, (SELECT id FROM filieres WHERE nom = 'Nutrition')),
(1, (SELECT id FROM filieres WHERE nom = 'Génie Civil')),
(1, (SELECT id FROM filieres WHERE nom = 'Architecture')),
(1, (SELECT id FROM filieres WHERE nom = 'Urbanisme')),
(1, (SELECT id FROM filieres WHERE nom = 'Topographie')),
(1, (SELECT id FROM filieres WHERE nom = 'Hydraulique')),
(1, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(1, (SELECT id FROM filieres WHERE nom = 'Finance')),
(1, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(1, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines')),
(1, (SELECT id FROM filieres WHERE nom = 'Management')),
(1, (SELECT id FROM filieres WHERE nom = 'Entrepreneuriat')),
(1, (SELECT id FROM filieres WHERE nom = 'Commerce International')),
(1, (SELECT id FROM filieres WHERE nom = 'Droit des Affaires')),
(1, (SELECT id FROM filieres WHERE nom = 'Droit International')),
(1, (SELECT id FROM filieres WHERE nom = 'Administration Publique'));

-- Université de Parakou (id 2) : Agronomie, Génie Civil, Gestion
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(2, (SELECT id FROM filieres WHERE nom = 'Génie Civil')),
(2, (SELECT id FROM filieres WHERE nom = 'Architecture')),
(2, (SELECT id FROM filieres WHERE nom = 'Urbanisme')),
(2, (SELECT id FROM filieres WHERE nom = 'Topographie')),
(2, (SELECT id FROM filieres WHERE nom = 'Hydraulique')),
(2, (SELECT id FROM filieres WHERE nom = 'Agronomie')),
(2, (SELECT id FROM filieres WHERE nom = 'Production Végétale')),
(2, (SELECT id FROM filieres WHERE nom = 'Production Animale')),
(2, (SELECT id FROM filieres WHERE nom = 'Agroalimentaire')),
(2, (SELECT id FROM filieres WHERE nom = 'Développement Rural')),
(2, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(2, (SELECT id FROM filieres WHERE nom = 'Finance')),
(2, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(2, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines')),
(2, (SELECT id FROM filieres WHERE nom = 'Management'));

-- EPITECH Bénin (id 8) : Informatique uniquement
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(8, (SELECT id FROM filieres WHERE nom = 'Génie Logiciel')),
(8, (SELECT id FROM filieres WHERE nom = 'Développement Web')),
(8, (SELECT id FROM filieres WHERE nom = 'Développement Mobile')),
(8, (SELECT id FROM filieres WHERE nom = 'Cybersécurité')),
(8, (SELECT id FROM filieres WHERE nom = 'Intelligence Artificielle')),
(8, (SELECT id FROM filieres WHERE nom = 'Data Science')),
(8, (SELECT id FROM filieres WHERE nom = 'Big Data')),
(8, (SELECT id FROM filieres WHERE nom = 'Cloud Computing')),
(8, (SELECT id FROM filieres WHERE nom = 'Réseaux et Télécommunications')),
(8, (SELECT id FROM filieres WHERE nom = 'Robotique'));

-- ESGIS Bénin (id 3) : Informatique, Gestion, Communication
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(3, (SELECT id FROM filieres WHERE nom = 'Génie Logiciel')),
(3, (SELECT id FROM filieres WHERE nom = 'Développement Web')),
(3, (SELECT id FROM filieres WHERE nom = 'Développement Mobile')),
(3, (SELECT id FROM filieres WHERE nom = 'Cybersécurité')),
(3, (SELECT id FROM filieres WHERE nom = 'Intelligence Artificielle')),
(3, (SELECT id FROM filieres WHERE nom = 'Data Science')),
(3, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(3, (SELECT id FROM filieres WHERE nom = 'Finance')),
(3, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(3, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines')),
(3, (SELECT id FROM filieres WHERE nom = 'Management')),
(3, (SELECT id FROM filieres WHERE nom = 'Entrepreneuriat')),
(3, (SELECT id FROM filieres WHERE nom = 'Journalisme')),
(3, (SELECT id FROM filieres WHERE nom = 'Communication Digitale')),
(3, (SELECT id FROM filieres WHERE nom = 'Marketing Digital'));

-- HECM (id 4) : Gestion, Droit, Communication
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(4, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(4, (SELECT id FROM filieres WHERE nom = 'Finance')),
(4, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(4, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines')),
(4, (SELECT id FROM filieres WHERE nom = 'Management')),
(4, (SELECT id FROM filieres WHERE nom = 'Droit des Affaires')),
(4, (SELECT id FROM filieres WHERE nom = 'Droit International')),
(4, (SELECT id FROM filieres WHERE nom = 'Administration Publique')),
(4, (SELECT id FROM filieres WHERE nom = 'Journalisme')),
(4, (SELECT id FROM filieres WHERE nom = 'Communication Digitale')),
(4, (SELECT id FROM filieres WHERE nom = 'Marketing Digital'));

-- CERCO (id 5) : Informatique, Gestion
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(5, (SELECT id FROM filieres WHERE nom = 'Génie Logiciel')),
(5, (SELECT id FROM filieres WHERE nom = 'Développement Web')),
(5, (SELECT id FROM filieres WHERE nom = 'Développement Mobile')),
(5, (SELECT id FROM filieres WHERE nom = 'Cybersécurité')),
(5, (SELECT id FROM filieres WHERE nom = 'Intelligence Artificielle')),
(5, (SELECT id FROM filieres WHERE nom = 'Data Science')),
(5, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(5, (SELECT id FROM filieres WHERE nom = 'Finance')),
(5, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(5, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines'));

-- Université de Lomé (id 9) : Droit, Gestion, Santé, Sciences
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(9, (SELECT id FROM filieres WHERE nom = 'Médecine Générale')),
(9, (SELECT id FROM filieres WHERE nom = 'Pharmacie')),
(9, (SELECT id FROM filieres WHERE nom = 'Soins Infirmiers')),
(9, (SELECT id FROM filieres WHERE nom = 'Sage-Femme')),
(9, (SELECT id FROM filieres WHERE nom = 'Kinésithérapie')),
(9, (SELECT id FROM filieres WHERE nom = 'Nutrition')),
(9, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(9, (SELECT id FROM filieres WHERE nom = 'Finance')),
(9, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(9, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines')),
(9, (SELECT id FROM filieres WHERE nom = 'Management')),
(9, (SELECT id FROM filieres WHERE nom = 'Droit des Affaires')),
(9, (SELECT id FROM filieres WHERE nom = 'Droit International')),
(9, (SELECT id FROM filieres WHERE nom = 'Administration Publique')),
(9, (SELECT id FROM filieres WHERE nom = 'Mathématiques')),
(9, (SELECT id FROM filieres WHERE nom = 'Physique')),
(9, (SELECT id FROM filieres WHERE nom = 'Chimie')),
(9, (SELECT id FROM filieres WHERE nom = 'Biologie'));

-- Université de Kara (id 10) : Enseignement, Communication
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(10, (SELECT id FROM filieres WHERE nom = 'Enseignement Mathématiques')),
(10, (SELECT id FROM filieres WHERE nom = 'Enseignement Sciences')),
(10, (SELECT id FROM filieres WHERE nom = 'Sciences de l\'Éducation')),
(10, (SELECT id FROM filieres WHERE nom = 'Journalisme')),
(10, (SELECT id FROM filieres WHERE nom = 'Communication Digitale')),
(10, (SELECT id FROM filieres WHERE nom = 'Marketing Digital'));

-- UCAD Sénégal (id 18) : Médecine, Sciences, Droit, Gestion
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(18, (SELECT id FROM filieres WHERE nom = 'Médecine Générale')),
(18, (SELECT id FROM filieres WHERE nom = 'Pharmacie')),
(18, (SELECT id FROM filieres WHERE nom = 'Soins Infirmiers')),
(18, (SELECT id FROM filieres WHERE nom = 'Sage-Femme')),
(18, (SELECT id FROM filieres WHERE nom = 'Kinésithérapie')),
(18, (SELECT id FROM filieres WHERE nom = 'Nutrition')),
(18, (SELECT id FROM filieres WHERE nom = 'Mathématiques')),
(18, (SELECT id FROM filieres WHERE nom = 'Physique')),
(18, (SELECT id FROM filieres WHERE nom = 'Chimie')),
(18, (SELECT id FROM filieres WHERE nom = 'Biologie')),
(18, (SELECT id FROM filieres WHERE nom = 'Droit des Affaires')),
(18, (SELECT id FROM filieres WHERE nom = 'Droit International')),
(18, (SELECT id FROM filieres WHERE nom = 'Administration Publique')),
(18, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(18, (SELECT id FROM filieres WHERE nom = 'Finance')),
(18, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(18, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines'));

-- Université Félix Houphouët-Boigny (id 21) : Droit, Gestion, Santé, Communication
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(21, (SELECT id FROM filieres WHERE nom = 'Médecine Générale')),
(21, (SELECT id FROM filieres WHERE nom = 'Pharmacie')),
(21, (SELECT id FROM filieres WHERE nom = 'Soins Infirmiers')),
(21, (SELECT id FROM filieres WHERE nom = 'Sage-Femme')),
(21, (SELECT id FROM filieres WHERE nom = 'Kinésithérapie')),
(21, (SELECT id FROM filieres WHERE nom = 'Nutrition')),
(21, (SELECT id FROM filieres WHERE nom = 'Droit des Affaires')),
(21, (SELECT id FROM filieres WHERE nom = 'Droit International')),
(21, (SELECT id FROM filieres WHERE nom = 'Administration Publique')),
(21, (SELECT id FROM filieres WHERE nom = 'Comptabilité')),
(21, (SELECT id FROM filieres WHERE nom = 'Finance')),
(21, (SELECT id FROM filieres WHERE nom = 'Marketing')),
(21, (SELECT id FROM filieres WHERE nom = 'Gestion des Ressources Humaines')),
(21, (SELECT id FROM filieres WHERE nom = 'Management')),
(21, (SELECT id FROM filieres WHERE nom = 'Journalisme')),
(21, (SELECT id FROM filieres WHERE nom = 'Communication Digitale')),
(21, (SELECT id FROM filieres WHERE nom = 'Marketing Digital'));

-- =====================================================
-- 5. QUESTIONS DU QUIZ (22 questions)
-- =====================================================
INSERT INTO quiz_questions (numero, question_text, type_reponse, est_plaisir, ordre) VALUES
(1, 'Tu es en quelle série ou filière actuellement ?', 'radio', 0, 1),
(2, 'Quelle est ta moyenne générale approximative ?', 'radio', 0, 2),
(3, 'Quelle est ta matière préférée ?', 'radio', 0, 3),
(4, 'Quelle activité te donne le plus d''énergie ?', 'carte', 0, 4),
(5, 'Quand tu travailles sur un projet, tu préfères ?', 'carte', 0, 5),
(6, 'Quel type de problème aimes-tu résoudre ?', 'carte', 0, 6),
(7, 'Dans quel environnement te sens-tu le plus à l''aise ?', 'radio', 0, 7),
(8, 'Tu préfères travailler avec ?', 'carte', 0, 8),
(9, 'Quel secteur t''attire le plus ?', 'carte', 0, 9),
(10, 'Combien d''années d''études es-tu prêt à faire ?', 'radio', 0, 10),
(11, 'Quel est ton objectif principal dans ta future carrière ?', 'carte', 0, 11),
(12, 'Tu te considères plutôt comme ?', 'carte', 0, 12),
(13, 'Si tu pouvais choisir un stage demain, lequel choisirais-tu ?', 'carte', 0, 13),
(14, 'Quel outil aimerais-tu maîtriser parfaitement ?', 'carte', 0, 14),
(15, 'Quel type d''impact aimerais-tu avoir sur la société ?', 'carte', 0, 15),
(16, 'Face à un défi important, tu fais quoi généralement ?', 'radio', 0, 16),
(17, 'Tu préfères apprendre de quelle manière ?', 'radio', 0, 17),
(18, 'Quel est ton principal point fort ?', 'carte', 0, 18),
(19, 'Dans quel domaine te vois-tu le plus dans 10 ans ?', 'carte', 0, 19),
(20, 'Dans quel pays ou zone aimerais-tu travailler plus tard ?', 'radio', 0, 20),
(21, 'Quel est ton hobby préféré ?', 'carte', 1, 21),
(22, 'Ton voyage de rêve serait plutôt ?', 'carte', 1, 22);

-- =====================================================
-- 6. OPTIONS DU QUIZ AVEC SCORES JSON
-- =====================================================

-- Q1: Série (Coef 3)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(1, 'bac_a', 'BAC A (Littéraire)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":2, "droit":3, "enseignement":4, "agronomie":0, "communication":4, "sciences_fond":0, "arts":3}', 1),
(1, 'bac_b', 'BAC B (Économique)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":4, "droit":2, "enseignement":2, "agronomie":3, "communication":2, "sciences_fond":0, "arts":0}', 2),
(1, 'bac_c', 'BAC C (Maths/Physique)', '{"informatique":4, "sante":3, "genie_civil":5, "gestion":3, "droit":0, "enseignement":3, "agronomie":4, "communication":0, "sciences_fond":5, "arts":0}', 3),
(1, 'bac_d', 'BAC D (SVT)', '{"informatique":2, "sante":5, "genie_civil":2, "gestion":2, "droit":0, "enseignement":4, "agronomie":3, "communication":0, "sciences_fond":4, "arts":0}', 4),
(1, 'bac_e', 'BAC E (Technique)', '{"informatique":3, "sante":2, "genie_civil":5, "gestion":2, "droit":0, "enseignement":2, "agronomie":3, "communication":0, "sciences_fond":3, "arts":0}', 5),
(1, 'f2', 'F2 (Agronomie)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":2, "droit":0, "enseignement":0, "agronomie":5, "communication":0, "sciences_fond":2, "arts":0}', 6),
(1, 'f3', 'F3 (Gestion)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":5, "droit":2, "enseignement":0, "agronomie":0, "communication":3, "sciences_fond":0, "arts":0}', 7),
(1, 'f4', 'F4 (Communication)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":3, "droit":2, "enseignement":0, "agronomie":0, "communication":5, "sciences_fond":0, "arts":3}', 8),
(1, 'dwm', 'DWM (Informatique)', '{"informatique":5, "sante":0, "genie_civil":0, "gestion":2, "droit":0, "enseignement":2, "agronomie":0, "communication":2, "sciences_fond":2, "arts":0}', 9),
(1, 'tea', 'TEA (Technique)', '{"informatique":0, "sante":0, "genie_civil":5, "gestion":2, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":2, "arts":0}', 10),
(1, 'pm', 'PM (Gestion/Commerce)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":5, "droit":0, "enseignement":0, "agronomie":0, "communication":3, "sciences_fond":0, "arts":0}', 11),
(1, 'mmv', 'MMV (Métiers de la mode)', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":2, "droit":0, "enseignement":0, "agronomie":0, "communication":4, "sciences_fond":0, "arts":5}', 12),
(1, 'autre_serie', 'Autre', '{"informatique":2, "sante":2, "genie_civil":2, "gestion":2, "droit":2, "enseignement":2, "agronomie":2, "communication":2, "sciences_fond":2, "arts":2}', 13);

-- Q2: Moyenne (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(2, 'moins_10', 'Moins de 10', '{"informatique":1, "sante":1, "genie_civil":1, "gestion":1, "droit":1, "enseignement":1, "agronomie":1, "communication":1, "sciences_fond":1, "arts":1}', 1),
(2, '10_12', '10 - 12', '{"informatique":2, "sante":2, "genie_civil":2, "gestion":2, "droit":2, "enseignement":2, "agronomie":2, "communication":2, "sciences_fond":2, "arts":2}', 2),
(2, '12_14', '12 - 14', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3, "sciences_fond":3, "arts":3}', 3),
(2, '14_plus', '14 et plus', '{"informatique":4, "sante":4, "genie_civil":4, "gestion":4, "droit":4, "enseignement":4, "agronomie":4, "communication":4, "sciences_fond":4, "arts":4}', 4);

-- Q3: Matière préférée (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(3, 'maths', 'Mathématiques', '{"informatique":4, "sante":2, "genie_civil":5, "gestion":4, "droit":1, "enseignement":4, "agronomie":4, "communication":0, "sciences_fond":5, "arts":0}', 1),
(3, 'francais', 'Français / Littérature', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":3, "droit":4, "enseignement":5, "agronomie":0, "communication":5, "sciences_fond":0, "arts":4}', 2),
(3, 'sciences_physiques', 'Sciences Physiques', '{"informatique":3, "sante":4, "genie_civil":5, "gestion":2, "droit":0, "enseignement":3, "agronomie":4, "communication":0, "sciences_fond":5, "arts":0}', 3),
(3, 'informatique', 'Informatique', '{"informatique":5, "sante":0, "genie_civil":2, "gestion":2, "droit":0, "enseignement":2, "agronomie":0, "communication":3, "sciences_fond":3, "arts":0}', 4),
(3, 'anglais', 'Anglais', '{"informatique":2, "sante":1, "genie_civil":1, "gestion":4, "droit":3, "enseignement":4, "agronomie":1, "communication":5, "sciences_fond":0, "arts":2}', 5),
(3, 'svt', 'SVT (Sciences de la Vie)', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":1, "droit":0, "enseignement":4, "agronomie":5, "communication":1, "sciences_fond":4, "arts":0}', 6),
(3, 'dessin', 'Dessin / Arts', '{"informatique":2, "sante":0, "genie_civil":4, "gestion":1, "droit":0, "enseignement":1, "agronomie":2, "communication":3, "sciences_fond":0, "arts":5}', 7),
(3, 'autre_matiere', 'Autre', '{"informatique":2, "sante":2, "genie_civil":2, "gestion":2, "droit":2, "enseignement":2, "agronomie":2, "communication":2, "sciences_fond":2, "arts":2}', 8);

-- Q4: Activité qui donne de l'énergie (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(4, 'coder', 'Coder / Programmer', 'fa-code', '{"informatique":5, "sante":0, "genie_civil":0, "gestion":2, "droit":0, "enseignement":0, "agronomie":0, "communication":2, "sciences_fond":2, "arts":0}', 1),
(4, 'soigner', 'Soigner / Aider', 'fa-hand-holding-heart', '{"informatique":0, "sante":5, "genie_civil":0, "gestion":0, "droit":0, "enseignement":2, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 2),
(4, 'construire', 'Construire / Créer', 'fa-hammer', '{"informatique":2, "sante":0, "genie_civil":5, "gestion":1, "droit":0, "enseignement":0, "agronomie":3, "communication":0, "sciences_fond":0, "arts":0}', 3),
(4, 'vendre', 'Vendre / Négocier', 'fa-handshake', '{"informatique":1, "sante":0, "genie_civil":0, "gestion":5, "droit":2, "enseignement":0, "agronomie":0, "communication":4, "sciences_fond":0, "arts":0}', 4),
(4, 'enseigner', 'Enseigner / Transmettre', 'fa-chalkboard-user', '{"informatique":1, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":5, "agronomie":0, "communication":3, "sciences_fond":2, "arts":0}', 5),
(4, 'creer_art', 'Créer / Imaginer', 'fa-palette', '{"informatique":3, "sante":0, "genie_civil":0, "gestion":2, "droit":0, "enseignement":2, "agronomie":0, "communication":4, "sciences_fond":0, "arts":5}', 6);

-- Q5: Quand tu travailles sur un projet, tu préfères ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(5, 'analyse', 'Analyser des données', 'fa-chart-simple', '{"informatique":5, "sante":2, "genie_civil":3, "gestion":4, "droit":3, "enseignement":2, "agronomie":2, "communication":2, "sciences_fond":4, "arts":0}', 1),
(5, 'concevoir', 'Concevoir / Innover', 'fa-lightbulb', '{"informatique":4, "sante":2, "genie_civil":5, "gestion":3, "droit":2, "enseignement":3, "agronomie":3, "communication":3, "sciences_fond":3, "arts":4}', 2),
(5, 'organiser', 'Organiser / Planifier', 'fa-list-check', '{"informatique":2, "sante":3, "genie_civil":4, "gestion":5, "droit":4, "enseignement":4, "agronomie":3, "communication":3, "sciences_fond":0, "arts":0}', 3),
(5, 'creer_contenu', 'Créer du contenu', 'fa-pen-fancy', '{"informatique":2, "sante":0, "genie_civil":0, "gestion":3, "droit":2, "enseignement":4, "agronomie":0, "communication":5, "sciences_fond":0, "arts":5}', 4);

-- Q6: Quel type de problème aimes-tu résoudre ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(6, 'technique', 'Problèmes techniques', 'fa-gear', '{"informatique":5, "sante":0, "genie_civil":5, "gestion":2, "droit":0, "enseignement":0, "agronomie":3, "communication":0, "sciences_fond":4, "arts":0}', 1),
(6, 'humain', 'Problèmes humains / sociaux', 'fa-users', '{"informatique":0, "sante":5, "genie_civil":0, "gestion":4, "droit":5, "enseignement":5, "agronomie":0, "communication":5, "sciences_fond":0, "arts":2}', 2),
(6, 'scientifique', 'Problèmes scientifiques', 'fa-flask', '{"informatique":3, "sante":4, "genie_civil":3, "gestion":2, "droit":0, "enseignement":3, "agronomie":4, "communication":0, "sciences_fond":5, "arts":0}', 3),
(6, 'creatif', 'Problèmes créatifs', 'fa-palette', '{"informatique":3, "sante":0, "genie_civil":2, "gestion":3, "droit":0, "enseignement":2, "agronomie":0, "communication":4, "sciences_fond":0, "arts":5}', 4);

-- Q7: Dans quel environnement te sens-tu le plus à l'aise ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(7, 'bureau', 'Bureau / Ordinateur', '{"informatique":5, "sante":2, "genie_civil":2, "gestion":4, "droit":4, "enseignement":3, "agronomie":1, "communication":4, "sciences_fond":2, "arts":2}', 1),
(7, 'terrain', 'Terrain / Extérieur', '{"informatique":1, "sante":3, "genie_civil":5, "gestion":2, "droit":1, "enseignement":2, "agronomie":5, "communication":2, "sciences_fond":2, "arts":3}', 2),
(7, 'laboratoire', 'Laboratoire / Recherche', '{"informatique":3, "sante":5, "genie_civil":3, "gestion":1, "droit":1, "enseignement":3, "agronomie":4, "communication":1, "sciences_fond":5, "arts":0}', 3),
(7, 'hybride', 'Mixte / Flexible', '{"informatique":4, "sante":3, "genie_civil":3, "gestion":4, "droit":3, "enseignement":4, "agronomie":3, "communication":4, "sciences_fond":3, "arts":3}', 4);

-- Q8: Tu préfères travailler avec ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(8, 'machines', 'Des machines / outils', 'fa-gears', '{"informatique":3, "sante":1, "genie_civil":5, "gestion":1, "droit":0, "enseignement":0, "agronomie":5, "communication":0, "sciences_fond":2, "arts":0}', 1),
(8, 'chiffres', 'Des chiffres / données', 'fa-chart-simple', '{"informatique":5, "sante":2, "genie_civil":3, "gestion":5, "droit":3, "enseignement":2, "agronomie":3, "communication":1, "sciences_fond":5, "arts":0}', 2),
(8, 'personnes', 'Des personnes / clients', 'fa-users', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":5, "droit":5, "enseignement":5, "agronomie":2, "communication":5, "sciences_fond":0, "arts":3}', 3),
(8, 'mots_images', 'Des mots / images', 'fa-image', '{"informatique":2, "sante":1, "genie_civil":0, "gestion":3, "droit":4, "enseignement":4, "agronomie":1, "communication":5, "sciences_fond":0, "arts":5}', 4),
(8, 'nature', 'Des plantes / animaux / nature', 'fa-leaf', '{"informatique":0, "sante":2, "genie_civil":2, "gestion":1, "droit":1, "enseignement":3, "agronomie":5, "communication":1, "sciences_fond":3, "arts":0}', 5);

-- Q9: Quel secteur t'attire le plus ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(9, 'tech', 'Tech / Numérique', 'fa-microchip', '{"informatique":5, "sante":0, "genie_civil":2, "gestion":3, "droit":0, "enseignement":0, "agronomie":0, "communication":3, "sciences_fond":2, "arts":0}', 1),
(9, 'sante', 'Santé / Bien-être', 'fa-heart', '{"informatique":0, "sante":5, "genie_civil":0, "gestion":2, "droit":2, "enseignement":3, "agronomie":2, "communication":0, "sciences_fond":2, "arts":0}', 2),
(9, 'finance', 'Finance / Banque', 'fa-coins', '{"informatique":2, "sante":0, "genie_civil":0, "gestion":5, "droit":4, "enseignement":0, "agronomie":0, "communication":2, "sciences_fond":0, "arts":0}', 3),
(9, 'education', 'Éducation / Formation', 'fa-school', '{"informatique":2, "sante":2, "genie_civil":0, "gestion":2, "droit":3, "enseignement":5, "agronomie":0, "communication":3, "sciences_fond":3, "arts":0}', 4),
(9, 'construction', 'Construction / Infrastructure', 'fa-building', '{"informatique":1, "sante":0, "genie_civil":5, "gestion":2, "droit":0, "enseignement":0, "agronomie":2, "communication":0, "sciences_fond":0, "arts":0}', 5),
(9, 'agriculture', 'Agriculture / Environnement', 'fa-tree', '{"informatique":0, "sante":0, "genie_civil":2, "gestion":0, "droit":0, "enseignement":2, "agronomie":5, "communication":0, "sciences_fond":3, "arts":0}', 6);

-- Q10: Combien d'années d'études es-tu prêt à faire ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(10, '1_an', '1 an (Formation courte)', '{"informatique":2, "sante":1, "genie_civil":1, "gestion":3, "droit":1, "enseignement":2, "agronomie":2, "communication":3, "sciences_fond":1, "arts":3}', 1),
(10, '2_ans', '2 ans (BTS/DUT)', '{"informatique":3, "sante":2, "genie_civil":2, "gestion":4, "droit":2, "enseignement":3, "agronomie":3, "communication":4, "sciences_fond":2, "arts":4}', 2),
(10, '3_ans', '3 ans (Licence)', '{"informatique":4, "sante":3, "genie_civil":4, "gestion":4, "droit":4, "enseignement":4, "agronomie":4, "communication":4, "sciences_fond":4, "arts":4}', 3),
(10, '5_ans', '5 ans (Master/Ingénieur)', '{"informatique":5, "sante":4, "genie_civil":5, "gestion":4, "droit":5, "enseignement":4, "agronomie":4, "communication":3, "sciences_fond":4, "arts":3}', 4),
(10, 'plus_5', 'Plus de 5 ans (Doctorat)', '{"informatique":4, "sante":5, "genie_civil":3, "gestion":3, "droit":5, "enseignement":5, "agronomie":3, "communication":2, "sciences_fond":5, "arts":2}', 5);

-- Q11: Objectif principal dans ta future carrière ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(11, 'argent', 'Gagner beaucoup d\'argent', 'fa-coins', '{"informatique":4, "sante":2, "genie_civil":4, "gestion":5, "droit":4, "enseignement":1, "agronomie":2, "communication":4, "sciences_fond":2, "arts":2}', 1),
(11, 'aider', 'Aider les autres', 'fa-hand-holding-heart', '{"informatique":2, "sante":5, "genie_civil":2, "gestion":2, "droit":4, "enseignement":5, "agronomie":4, "communication":3, "sciences_fond":3, "arts":2}', 2),
(11, 'patron', 'Être mon propre patron', 'fa-user-tie', '{"informatique":4, "sante":1, "genie_civil":3, "gestion":5, "droit":3, "enseignement":1, "agronomie":3, "communication":4, "sciences_fond":1, "arts":4}', 3),
(11, 'stabilite', 'Stabilité de l\'emploi', 'fa-shield', '{"informatique":3, "sante":4, "genie_civil":4, "gestion":4, "droit":5, "enseignement":5, "agronomie":4, "communication":3, "sciences_fond":4, "arts":3}', 4),
(11, 'innovation', 'Innover / Créer', 'fa-lightbulb', '{"informatique":5, "sante":2, "genie_civil":3, "gestion":4, "droit":2, "enseignement":3, "agronomie":3, "communication":4, "sciences_fond":4, "arts":5}', 5);

-- Q12: Tu te considères plutôt comme ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(12, 'analytique', 'Analytique / Logique', 'fa-brain', '{"informatique":5, "sante":3, "genie_civil":4, "gestion":4, "droit":4, "enseignement":2, "agronomie":3, "communication":1, "sciences_fond":5, "arts":1}', 1),
(12, 'creatif', 'Créatif / Imaginatif', 'fa-palette', '{"informatique":3, "sante":1, "genie_civil":2, "gestion":3, "droit":2, "enseignement":4, "agronomie":2, "communication":5, "sciences_fond":2, "arts":5}', 2),
(12, 'communicatif', 'Communicatif / Sociable', 'fa-comments', '{"informatique":2, "sante":4, "genie_civil":1, "gestion":5, "droit":5, "enseignement":5, "agronomie":2, "communication":5, "sciences_fond":1, "arts":3}', 3),
(12, 'manuel', 'Manuel / Pratique', 'fa-tools', '{"informatique":2, "sante":3, "genie_civil":5, "gestion":2, "droit":1, "enseignement":3, "agronomie":4, "communication":1, "sciences_fond":2, "arts":4}', 4),
(12, 'organise', 'Organisé / Structuré', 'fa-list-check', '{"informatique":3, "sante":2, "genie_civil":4, "gestion":5, "droit":4, "enseignement":4, "agronomie":3, "communication":3, "sciences_fond":3, "arts":2}', 5);

-- Q13: Si tu pouvais choisir un stage demain, lequel choisirais-tu ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(13, 'hopital', 'À l\'hôpital', 'fa-hospital', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":1, "droit":2, "enseignement":2, "agronomie":1, "communication":1, "sciences_fond":2, "arts":0}', 1),
(13, 'cabinet', 'En cabinet d\'avocats', 'fa-building', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":3, "droit":5, "enseignement":2, "agronomie":0, "communication":3, "sciences_fond":0, "arts":0}', 2),
(13, 'startup', 'En startup tech', 'fa-rocket', '{"informatique":5, "sante":0, "genie_civil":2, "gestion":4, "droit":0, "enseignement":1, "agronomie":0, "communication":4, "sciences_fond":2, "arts":2}', 3),
(13, 'ecole', 'À l\'école / Université', 'fa-school', '{"informatique":2, "sante":2, "genie_civil":1, "gestion":2, "droit":2, "enseignement":5, "agronomie":2, "communication":3, "sciences_fond":3, "arts":2}', 4),
(13, 'chantier', 'Sur un chantier', 'fa-hard-hat', '{"informatique":1, "sante":0, "genie_civil":5, "gestion":2, "droit":0, "enseignement":0, "agronomie":2, "communication":0, "sciences_fond":0, "arts":0}', 5);

-- Q14: Quel outil aimerais-tu maîtriser parfaitement ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(14, 'code', 'Code / Programmation', 'fa-code', '{"informatique":5, "sante":0, "genie_civil":2, "gestion":2, "droit":0, "enseignement":2, "agronomie":1, "communication":3, "sciences_fond":3, "arts":1}', 1),
(14, 'caeo', 'CAO / Design 3D', 'fa-cube', '{"informatique":3, "sante":0, "genie_civil":5, "gestion":1, "droit":0, "enseignement":1, "agronomie":3, "communication":2, "sciences_fond":1, "arts":4}', 2),
(14, 'bureautique', 'Bureautique / Gestion', 'fa-file-excel', '{"informatique":2, "sante":1, "genie_civil":2, "gestion":5, "droit":4, "enseignement":3, "agronomie":2, "communication":3, "sciences_fond":1, "arts":0}', 3),
(14, 'multimedia', 'Montage / Multimédia', 'fa-video', '{"informatique":3, "sante":0, "genie_civil":0, "gestion":2, "droit":0, "enseignement":3, "agronomie":1, "communication":5, "sciences_fond":1, "arts":5}', 4),
(14, 'instrument', 'Instrument médical', 'fa-stethoscope', '{"informatique":0, "sante":5, "genie_civil":0, "gestion":0, "droit":0, "enseignement":2, "agronomie":2, "communication":0, "sciences_fond":3, "arts":0}', 5);

-- Q15: Impact sur la société (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(15, 'sante', 'Améliorer la santé', 'fa-heartbeat', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":1, "droit":2, "enseignement":3, "agronomie":2, "communication":1, "sciences_fond":3, "arts":0}', 1),
(15, 'environnement', 'Protéger l\'environnement', 'fa-leaf', '{"informatique":2, "sante":2, "genie_civil":3, "gestion":2, "droit":2, "enseignement":3, "agronomie":5, "communication":2, "sciences_fond":4, "arts":2}', 2),
(15, 'education', 'Améliorer l\'éducation', 'fa-graduation-cap', '{"informatique":2, "sante":2, "genie_civil":1, "gestion":2, "droit":3, "enseignement":5, "agronomie":2, "communication":4, "sciences_fond":2, "arts":2}', 3),
(15, 'technologie', 'Faire avancer la technologie', 'fa-microchip', '{"informatique":5, "sante":2, "genie_civil":4, "gestion":3, "droit":0, "enseignement":2, "agronomie":2, "communication":3, "sciences_fond":5, "arts":0}', 4),
(15, 'justice', 'Promouvoir la justice', 'fa-scale-balanced', '{"informatique":0, "sante":1, "genie_civil":0, "gestion":3, "droit":5, "enseignement":3, "agronomie":0, "communication":3, "sciences_fond":0, "arts":0}', 5);

-- Q16: Face à un défi important, tu fais quoi ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(16, 'solution', 'Cherches une solution par toi-même', '{"informatique":5, "sante":4, "genie_civil":5, "gestion":4, "droit":4, "enseignement":4, "agronomie":4, "communication":4, "sciences_fond":5, "arts":4}', 1),
(16, 'aide', 'Demandes de l\'aide', '{"informatique":2, "sante":4, "genie_civil":2, "gestion":4, "droit":4, "enseignement":4, "agronomie":3, "communication":5, "sciences_fond":2, "arts":3}', 2),
(16, 'recherche', 'Fais des recherches', '{"informatique":4, "sante":3, "genie_civil":3, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3, "sciences_fond":5, "arts":3}', 3);

-- Q17: Tu préfères apprendre de quelle manière ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(17, 'visuel', 'Par la vue / Schémas', '{"informatique":4, "sante":3, "genie_civil":5, "gestion":3, "droit":3, "enseignement":4, "agronomie":4, "communication":4, "sciences_fond":4, "arts":5}', 1),
(17, 'auditif', 'Par l\'écoute / Cours', '{"informatique":2, "sante":3, "genie_civil":2, "gestion":3, "droit":4, "enseignement":5, "agronomie":2, "communication":5, "sciences_fond":2, "arts":3}', 2),
(17, 'pratique', 'Par la pratique / Exercices', '{"informatique":4, "sante":4, "genie_civil":4, "gestion":4, "droit":3, "enseignement":4, "agronomie":5, "communication":3, "sciences_fond":4, "arts":4}', 3);

-- Q18: Quel est ton principal point fort ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(18, 'logique', 'Logique / Raisonnement', 'fa-brain', '{"informatique":5, "sante":3, "genie_civil":5, "gestion":4, "droit":4, "enseignement":3, "agronomie":3, "communication":1, "sciences_fond":5, "arts":1}', 1),
(18, 'empathie', 'Empathie / Écoute', 'fa-heart', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":4, "droit":4, "enseignement":5, "agronomie":3, "communication":5, "sciences_fond":1, "arts":3}', 2),
(18, 'creativite', 'Créativité / Imagination', 'fa-palette', '{"informatique":3, "sante":1, "genie_civil":2, "gestion":3, "droit":2, "enseignement":4, "agronomie":2, "communication":5, "sciences_fond":2, "arts":5}', 3),
(18, 'organisation', 'Organisation / Planification', 'fa-list-check', '{"informatique":3, "sante":2, "genie_civil":4, "gestion":5, "droit":4, "enseignement":4, "agronomie":3, "communication":3, "sciences_fond":3, "arts":2}', 4),
(18, 'perseverance', 'Persévérance / Détermination', 'fa-rocket', '{"informatique":4, "sante":4, "genie_civil":4, "gestion":4, "droit":4, "enseignement":4, "agronomie":4, "communication":4, "sciences_fond":4, "arts":4}', 5);

-- Q19: Dans quel domaine te vois-tu dans 10 ans ? (Coef 2)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(19, 'expert_tech', 'Expert en technologie', 'fa-microchip', '{"informatique":5, "sante":0, "genie_civil":2, "gestion":2, "droit":0, "enseignement":0, "agronomie":0, "communication":2, "sciences_fond":3, "arts":0}', 1),
(19, 'medecin', 'Médecin / Professionnel de santé', 'fa-stethoscope', '{"informatique":0, "sante":5, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 2),
(19, 'chef_entreprise', 'Chef d\'entreprise', 'fa-chart-line', '{"informatique":3, "sante":0, "genie_civil":3, "gestion":5, "droit":2, "enseignement":0, "agronomie":2, "communication":4, "sciences_fond":0, "arts":2}', 3),
(19, 'enseignant', 'Enseignant / Formateur', 'fa-chalkboard-user', '{"informatique":2, "sante":2, "genie_civil":1, "gestion":2, "droit":3, "enseignement":5, "agronomie":2, "communication":3, "sciences_fond":4, "arts":2}', 4),
(19, 'architecte', 'Architecte / Ingénieur', 'fa-draw-polygon', '{"informatique":2, "sante":0, "genie_civil":5, "gestion":1, "droit":0, "enseignement":1, "agronomie":2, "communication":0, "sciences_fond":1, "arts":4}', 5);

-- Q20: Dans quel pays ou zone aimerais-tu travailler ? (Coef 1)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(20, 'benin', 'Bénin', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":3, "droit":4, "enseignement":4, "agronomie":4, "communication":3, "sciences_fond":3, "arts":3}', 1),
(20, 'afrique_ouest', 'Afrique de l\'Ouest', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3, "sciences_fond":3, "arts":3}', 2),
(20, 'europe', 'Europe', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3, "sciences_fond":3, "arts":3}', 3),
(20, 'international', 'Partout dans le monde', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3, "sciences_fond":3, "arts":3}', 4);

-- Q21: Hobby préféré (Plaisir - Coef 0)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(21, 'lecture', 'Lecture / Écriture', 'fa-book', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 1),
(21, 'sport', 'Sport / Fitness', 'fa-person-running', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 2),
(21, 'jeux', 'Jeux vidéo / Tech', 'fa-gamepad', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 3),
(21, 'art', 'Art / Musique', 'fa-palette', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 4);

-- Q22: Voyage de rêve (Plaisir - Coef 0)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(22, 'aventure', 'Aventure / Trek', 'fa-mountain', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 1),
(22, 'plage', 'Plage / Détente', 'fa-umbrella-beach', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 2),
(22, 'culture', 'Culture / Visites', 'fa-landmark', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 3),
(22, 'ville', 'Ville / Shopping', 'fa-city', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0, "sciences_fond":0, "arts":0}', 4);

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================