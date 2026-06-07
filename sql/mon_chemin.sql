-- =====================================================
-- BASE DE DONNÉES : Mon Chemin
-- Projet d'orientation scolaire avec validation admin
-- =====================================================
-- Auteur : Mon Chemin
-- Version : 1.0
-- =====================================================

-- Supprimer la base si elle existe (optionnel - à utiliser avec précaution)
-- DROP DATABASE IF EXISTS mon_chemin;

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS mon_chemin
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base
USE mon_chemin;

-- =====================================================
-- TABLE : utilisateurs (élèves et administrateurs)
-- =====================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(20),
    niveau VARCHAR(50) COMMENT 'Niveau scolaire (Terminale, Bac, DT, Licence 1, etc.)',
    situation_handicap BOOLEAN DEFAULT FALSE,
    type_handicap VARCHAR(100) NULL,
    parent_email VARCHAR(100),
    parent_whatsapp VARCHAR(20),
    role ENUM('eleve', 'admin') DEFAULT 'eleve',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME,
    actif BOOLEAN DEFAULT TRUE,
    reset_token VARCHAR(255) NULL,
    reset_expires DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : sessions (gestion des connexions)
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity INT,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : filieres (listes des filières possibles)
-- =====================================================
CREATE TABLE IF NOT EXISTS filieres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    domaine VARCHAR(50),
    description TEXT,
    debouches TEXT,
    duree_etudes VARCHAR(100),
    cout_moyen VARCHAR(100),
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_domaine (domaine)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : universites
-- =====================================================
CREATE TABLE IF NOT EXISTS universites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    localisation VARCHAR(100),
    site_web VARCHAR(255),
    logo VARCHAR(255),
    actif BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : universites_filieres (liaison université-filière)
-- =====================================================
CREATE TABLE IF NOT EXISTS universites_filieres (
    universite_id INT UNSIGNED NOT NULL,
    filiere_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (universite_id, filiere_id),
    FOREIGN KEY (universite_id) REFERENCES universites(id) ON DELETE CASCADE,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : quiz_questions (22 questions dont 2 plaisir)
-- =====================================================
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    question_text TEXT NOT NULL,
    type_reponse ENUM('radio', 'carte') DEFAULT 'radio',
    est_plaisir BOOLEAN DEFAULT FALSE,
    ordre INT DEFAULT 0,
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : quiz_options (options de réponse avec scores par domaine)
-- =====================================================
CREATE TABLE IF NOT EXISTS quiz_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    option_valeur VARCHAR(50) NOT NULL,
    option_texte VARCHAR(255) NOT NULL,
    option_icone VARCHAR(50),
    scores_domaines JSON NOT NULL COMMENT '{"informatique":5, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}',
    ordre INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : quiz_sessions (sessions de quiz des élèves)
-- =====================================================
CREATE TABLE IF NOT EXISTS quiz_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    date_debut DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_fin DATETIME NULL,
    statut ENUM('en_cours', 'termine', 'abandonne') DEFAULT 'en_cours',
    scores JSON COMMENT 'Scores par domaine calculés',
    filieres_proposees TEXT COMMENT 'IDs des filières recommandées séparés par virgules',
    statut_validation ENUM('en_attente', 'valide', 'rejete', 'publie') DEFAULT 'en_attente',
    commentaire_admin TEXT,
    date_validation DATETIME NULL,
    admin_id INT UNSIGNED NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES utilisateurs(id),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_statut (statut_validation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : quiz_reponses (réponses détaillées des élèves)
-- =====================================================
CREATE TABLE IF NOT EXISTS quiz_reponses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    question_numero INT NOT NULL,
    option_id INT UNSIGNED NOT NULL,
    reponse_valeur VARCHAR(50) NOT NULL,
    date_reponse DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id),
    FOREIGN KEY (option_id) REFERENCES quiz_options(id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : quiz_resultats_validations (historique des modifications admin)
-- =====================================================
CREATE TABLE IF NOT EXISTS quiz_resultats_validations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) COMMENT 'modification, validation, rejet, publication',
    filieres_avant TEXT,
    filieres_apres TEXT,
    commentaire TEXT,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES utilisateurs(id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : documents_pdf
-- =====================================================
CREATE TABLE IF NOT EXISTS documents_pdf (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    utilisateur_id INT UNSIGNED NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    taille_fichier INT,
    date_generation DATETIME DEFAULT CURRENT_TIMESTAMP,
    envoye_email BOOLEAN DEFAULT FALSE,
    envoye_whatsapp BOOLEAN DEFAULT FALSE,
    date_envoi_email DATETIME,
    date_envoi_whatsapp DATETIME,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    INDEX idx_utilisateur (utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : logs (traçabilité RGPD)
-- =====================================================
CREATE TABLE IF NOT EXISTS logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_date (date_action),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE : contact_messages (messages du formulaire de contact)
-- =====================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    sujet VARCHAR(200),
    message TEXT NOT NULL,
    statut ENUM('non_lu', 'lu', 'repondu') DEFAULT 'non_lu',
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTION DES DONNÉES DE BASE
-- =====================================================

-- Admin par défaut (mot de passe à hasher en PHP : "admin123")
INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, role) VALUES
('admin@monchemin.com', 'admin123', 'Admin', 'Système', 'admin');

-- Insertion des filières
INSERT INTO filieres (nom, domaine, description, debouches, duree_etudes, cout_moyen) VALUES
('Informatique', 'Informatique', 'Formation aux métiers du développement, de la data, de la cybersécurité et de l\'intelligence artificielle.', 'Développeur, Data Scientist, Administrateur réseau, DevOps, Consultant IT, Chef de projet digital', '3 ans (Licence) / 5 ans (Master) / 8 ans (Doctorat)', '150 000 - 500 000 FCFA/an'),
('Santé', 'Santé', 'Formation aux métiers de la médecine, pharmacie, soins infirmiers et professions paramédicales.', 'Médecin, Infirmier, Pharmacien, Chirurgien-dentiste, Sage-femme, Kinésithérapeute', '4 à 9 ans selon spécialité', '300 000 - 1 000 000 FCFA/an'),
('Génie Civil', 'Sciences', 'Formation à la conception, réalisation et maintenance des ouvrages d\'infrastructure.', 'Ingénieur BTP, Conducteur de travaux, Architecte, Urbaniste, Géomètre', '3 ans (Licence) / 5 ans (Master)', '150 000 - 400 000 FCFA/an'),
('Gestion', 'Économie', 'Formation au management d\'entreprise, finance, marketing et ressources humaines.', 'Cadre commercial, Chef d\'entreprise, Consultant, Contrôleur de gestion, Directeur marketing', '3 ans (Licence) / 5 ans (Master)', '150 000 - 350 000 FCFA/an'),
('Droit', 'Droit', 'Formation aux carrières juridiques et judiciaires, droit des affaires et droit international.', 'Avocat, Juge, Juriste d\'entreprise, Notaire, Conseiller juridique, Huissier', '3 ans (Licence) / 5 ans (Master)', '150 000 - 400 000 FCFA/an'),
('Enseignement', 'Éducation', 'Formation aux métiers de l\'éducation et de la pédagogie.', 'Professeur, Conseiller pédagogique, Formateur, Directeur d\'école, Inspecteur', '3 ans (Licence) / 5 ans (Master)', '100 000 - 250 000 FCFA/an'),
('Agronomie', 'Sciences', 'Formation aux sciences agricoles, agroalimentaires et environnementales.', 'Ingénieur agronome, Chef de projet agricole, Consultant en environnement, Technicien agricole', '3 ans (Licence) / 5 ans (Master)', '120 000 - 300 000 FCFA/an'),
('Communication', 'Lettres', 'Formation aux métiers de la communication, journalisme, publicité et médias sociaux.', 'Community manager, Journaliste, Responsable communication, Chef de projet digital, Attaché de presse', '3 ans (Licence) / 5 ans (Master)', '120 000 - 300 000 FCFA/an');

-- Insertion des universités
INSERT INTO universites (nom, description, localisation, site_web) VALUES
('Université d\'Abomey-Calavi', 'La plus grande université du Bénin, offrant une large gamme de formations.', 'Abomey-Calavi, Bénin', 'https://www.uac.bj'),
('Université de Parakou', 'Université de référence du nord du Bénin, spécialisée en sciences agronomiques.', 'Parakou, Bénin', 'https://www.up.bj'),
('Université de Lomé', 'Première université publique du Togo, offrant des formations variées.', 'Lomé, Togo', 'https://www.univ-lome.tg'),
('Université de Kara', 'Deuxième université publique du Togo, réputée pour ses formations en lettres.', 'Kara, Togo', 'https://www.uk.tg'),
('Université Félix Houphouët-Boigny', 'Grande université ivoirienne.', 'Abidjan, Côte d\'Ivoire', 'https://www.univ-fhb.ci');

-- Liaison universités - filières
INSERT INTO universites_filieres (universite_id, filiere_id) VALUES
(1, (SELECT id FROM filieres WHERE nom = 'Informatique')),
(1, (SELECT id FROM filieres WHERE nom = 'Droit')),
(1, (SELECT id FROM filieres WHERE nom = 'Gestion')),
(1, (SELECT id FROM filieres WHERE nom = 'Communication')),
(2, (SELECT id FROM filieres WHERE nom = 'Agronomie')),
(2, (SELECT id FROM filieres WHERE nom = 'Génie Civil')),
(3, (SELECT id FROM filieres WHERE nom = 'Santé')),
(3, (SELECT id FROM filieres WHERE nom = 'Droit')),
(3, (SELECT id FROM filieres WHERE nom = 'Informatique')),
(4, (SELECT id FROM filieres WHERE nom = 'Enseignement')),
(4, (SELECT id FROM filieres WHERE nom = 'Communication'));

-- =====================================================
-- INSERTION DES 22 QUESTIONS DU QUIZ
-- =====================================================

INSERT INTO quiz_questions (numero, question_text, type_reponse, est_plaisir, ordre) VALUES
(1, 'Quelle matière préfères-tu ?', 'carte', FALSE, 1),
(2, 'Tu préfères ?', 'radio', FALSE, 2),
(3, 'Quel environnement de travail t\'attire le plus ?', 'carte', FALSE, 3),
(4, 'Pendant ton temps libre, tu préfères ?', 'radio', FALSE, 4),
(5, 'Tu es plus ?', 'radio', FALSE, 5),
(6, 'Tu aimerais travailler ?', 'carte', FALSE, 6),
(7, 'Quelle activité te plaît le plus ?', 'carte', FALSE, 7),
(8, 'Tu préfères ?', 'radio', FALSE, 8),
(9, 'Si tu crées un projet, tu es plutôt ?', 'radio', FALSE, 9),
(10, 'Ton point fort est ?', 'carte', FALSE, 10),
(11, 'Tu préfères travailler ?', 'radio', FALSE, 11),
(12, 'Quel métier t\'impressionne le plus ?', 'carte', FALSE, 12),
(13, 'Quel problème aimerais-tu résoudre ?', 'carte', FALSE, 13),
(14, 'Quel outil aimerais-tu maîtriser ?', 'carte', FALSE, 14),
(15, 'Quel secteur t\'intéresse le plus ?', 'carte', FALSE, 15),
(16, 'Tu préfères apprendre ?', 'radio', FALSE, 16),
(17, 'Dans un groupe, tu es plutôt ?', 'radio', FALSE, 17),
(18, 'Quel stage choisirais-tu ?', 'carte', FALSE, 18),
(19, 'Quel est ton objectif principal ?', 'carte', FALSE, 19),
(20, 'Ton futur idéal ressemble à ?', 'carte', FALSE, 20),
(21, 'Ton hobby préféré ?', 'carte', TRUE, 21),
(22, 'Ton rêve de vacances ?', 'carte', TRUE, 22);

-- =====================================================
-- INSERTION DES OPTIONS POUR CHAQUE QUESTION
-- =====================================================

-- Question 1
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(1, 'maths', 'Mathématiques / Sciences', 'fa-calculator', '{"informatique":3, "sante":2, "genie_civil":4, "gestion":1, "droit":0, "enseignement":3, "agronomie":5, "communication":1}', 1),
(1, 'informatique', 'Informatique / Programmation', 'fa-laptop-code', '{"informatique":5, "sante":0, "genie_civil":2, "gestion":2, "droit":0, "enseignement":2, "agronomie":0, "communication":3}', 2),
(1, 'lettres', 'Lettres / Langues', 'fa-book', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":2, "droit":4, "enseignement":5, "agronomie":0, "communication":5}', 3),
(1, 'economie', 'Économie / Commerce', 'fa-chart-line', '{"informatique":1, "sante":0, "genie_civil":0, "gestion":5, "droit":3, "enseignement":2, "agronomie":1, "communication":4}', 4);

-- Question 2
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(2, 'theorique', 'Travail théorique / Réflexion', '{"informatique":4, "sante":3, "genie_civil":3, "gestion":2, "droit":4, "enseignement":4, "agronomie":2, "communication":2}', 1),
(2, 'pratique', 'Travail pratique / Manuel', '{"informatique":3, "sante":4, "genie_civil":5, "gestion":2, "droit":1, "enseignement":2, "agronomie":5, "communication":1}', 2),
(2, 'creatif', 'Travail créatif / Artistique', '{"informatique":2, "sante":1, "genie_civil":1, "gestion":3, "droit":2, "enseignement":3, "agronomie":1, "communication":5}', 3);

-- Question 3
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(3, 'bureau', 'Bureau / Calme', 'fa-building', '{"informatique":4, "sante":2, "genie_civil":2, "gestion":4, "droit":5, "enseignement":3, "agronomie":1, "communication":4}', 1),
(3, 'terrain', 'Terrain / Extérieur', 'fa-tree', '{"informatique":1, "sante":3, "genie_civil":5, "gestion":2, "droit":1, "enseignement":2, "agronomie":5, "communication":2}', 2),
(3, 'labo', 'Laboratoire / Recherche', 'fa-flask', '{"informatique":4, "sante":5, "genie_civil":4, "gestion":1, "droit":1, "enseignement":3, "agronomie":4, "communication":1}', 3),
(3, 'hybride', 'Mixte / Flexible', 'fa-arrows-spin', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":4, "droit":3, "enseignement":4, "agronomie":3, "communication":4}', 4);

-- Question 4
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(4, 'lire', 'Lire / Apprendre', '{"informatique":3, "sante":2, "genie_civil":2, "gestion":2, "droit":4, "enseignement":5, "agronomie":2, "communication":3}', 1),
(4, 'coder', 'Coder / Créer', '{"informatique":5, "sante":1, "genie_civil":2, "gestion":2, "droit":1, "enseignement":1, "agronomie":1, "communication":3}', 2),
(4, 'sport', 'Faire du sport', '{"informatique":1, "sante":4, "genie_civil":2, "gestion":1, "droit":1, "enseignement":2, "agronomie":3, "communication":2}', 3),
(4, 'sortir', 'Sortir avec des amis', '{"informatique":1, "sante":1, "genie_civil":1, "gestion":3, "droit":2, "enseignement":2, "agronomie":1, "communication":4}', 4);

-- Question 5
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(5, 'analytique', 'Analytique / Logique', '{"informatique":5, "sante":3, "genie_civil":4, "gestion":3, "droit":4, "enseignement":2, "agronomie":3, "communication":1}', 1),
(5, 'creatif', 'Créatif / Imaginatif', '{"informatique":3, "sante":1, "genie_civil":2, "gestion":3, "droit":2, "enseignement":4, "agronomie":2, "communication":5}', 2),
(5, 'social', 'Social / Aider les autres', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":2, "droit":3, "enseignement":5, "agronomie":3, "communication":4}', 3),
(5, 'organise', 'Organisé / Planificateur', '{"informatique":2, "sante":2, "genie_civil":4, "gestion":5, "droit":4, "enseignement":3, "agronomie":3, "communication":2}', 4);

-- Question 6
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(6, 'indep', 'Indépendant / Freelance', 'fa-user', '{"informatique":5, "sante":2, "genie_civil":3, "gestion":4, "droit":3, "enseignement":2, "agronomie":3, "communication":4}', 1),
(6, 'equipe', 'En équipe / Collaboration', 'fa-users', '{"informatique":3, "sante":5, "genie_civil":4, "gestion":4, "droit":4, "enseignement":5, "agronomie":4, "communication":5}', 2),
(6, 'mixte', 'Mixte / Variable', 'fa-people-arrows', '{"informatique":3, "sante":3, "genie_civil":3, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3}', 3);

-- Question 7
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(7, 'analyse', 'Analyser des données', 'fa-chart-simple', '{"informatique":5, "sante":2, "genie_civil":3, "gestion":4, "droit":3, "enseignement":2, "agronomie":2, "communication":2}', 1),
(7, 'concevoir', 'Concevoir / Innover', 'fa-lightbulb', '{"informatique":4, "sante":3, "genie_civil":5, "gestion":3, "droit":2, "enseignement":3, "agronomie":4, "communication":3}', 2),
(7, 'enseigner', 'Enseigner / Former', 'fa-chalkboard-user', '{"informatique":2, "sante":3, "genie_civil":2, "gestion":2, "droit":3, "enseignement":5, "agronomie":3, "communication":3}', 3),
(7, 'negocier', 'Négocier / Vendre', 'fa-handshake', '{"informatique":1, "sante":1, "genie_civil":1, "gestion":5, "droit":4, "enseignement":2, "agronomie":1, "communication":5}', 4);

-- Question 8
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(8, 'diriger', 'Diriger / Manager', '{"informatique":2, "sante":3, "genie_civil":4, "gestion":5, "droit":4, "enseignement":4, "agronomie":3, "communication":4}', 1),
(8, 'executer', 'Exécuter / Produire', '{"informatique":3, "sante":4, "genie_civil":5, "gestion":3, "droit":2, "enseignement":3, "agronomie":5, "communication":2}', 2),
(8, 'conseiller', 'Conseiller / Accompagner', '{"informatique":2, "sante":5, "genie_civil":2, "gestion":3, "droit":5, "enseignement":5, "agronomie":3, "communication":4}', 3);

-- Question 9
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(9, 'chef', 'Chef de projet / Leader', '{"informatique":3, "sante":3, "genie_civil":4, "gestion":5, "droit":4, "enseignement":4, "agronomie":3, "communication":4}', 1),
(9, 'tech', 'Tech / Développeur', '{"informatique":5, "sante":1, "genie_civil":4, "gestion":2, "droit":1, "enseignement":2, "agronomie":2, "communication":2}', 2),
(9, 'design', 'Design / Créatif', '{"informatique":3, "sante":1, "genie_civil":3, "gestion":2, "droit":1, "enseignement":2, "agronomie":1, "communication":5}', 3),
(9, 'support', 'Support / Organisation', '{"informatique":2, "sante":4, "genie_civil":2, "gestion":3, "droit":3, "enseignement":3, "agronomie":3, "communication":3}', 4);

-- Question 10
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(10, 'maths', 'Mathématiques', 'fa-square-root-variable', '{"informatique":4, "sante":2, "genie_civil":5, "gestion":3, "droit":2, "enseignement":4, "agronomie":5, "communication":1}', 1),
(10, 'langues', 'Langues / Communication', 'fa-language', '{"informatique":1, "sante":2, "genie_civil":1, "gestion":3, "droit":4, "enseignement":4, "agronomie":1, "communication":5}', 2),
(10, 'technique', 'Technique / Mécanique', 'fa-gear', '{"informatique":4, "sante":2, "genie_civil":5, "gestion":2, "droit":1, "enseignement":2, "agronomie":3, "communication":1}', 3),
(10, 'relationnel', 'Relationnel / Écoute', 'fa-heart', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":4, "droit":5, "enseignement":5, "agronomie":2, "communication":5}', 4);

-- Question 11
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(11, 'public', 'Dans le secteur public', '{"informatique":2, "sante":4, "genie_civil":4, "gestion":2, "droit":5, "enseignement":5, "agronomie":4, "communication":2}', 1),
(11, 'prive', 'Dans le secteur privé', '{"informatique":5, "sante":3, "genie_civil":4, "gestion":5, "droit":3, "enseignement":2, "agronomie":3, "communication":5}', 2),
(11, 'asso', 'Dans une association / ONG', '{"informatique":2, "sante":4, "genie_civil":3, "gestion":3, "droit":3, "enseignement":4, "agronomie":4, "communication":4}', 3);

-- Question 12
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(12, 'chirurgien', 'Chirurgien / Médecin', 'fa-stethoscope', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":1, "droit":1, "enseignement":2, "agronomie":1, "communication":1}', 1),
(12, 'architecte', 'Architecte / Ingénieur', 'fa-draw-polygon', '{"informatique":3, "sante":1, "genie_civil":5, "gestion":2, "droit":1, "enseignement":2, "agronomie":3, "communication":1}', 2),
(12, 'avocat', 'Avocat / Juge', 'fa-gavel', '{"informatique":1, "sante":1, "genie_civil":1, "gestion":3, "droit":5, "enseignement":2, "agronomie":1, "communication":3}', 3),
(12, 'entrepreneur', 'Entrepreneur / CEO', 'fa-chart-line', '{"informatique":4, "sante":1, "genie_civil":2, "gestion":5, "droit":2, "enseignement":1, "agronomie":2, "communication":4}', 4);

-- Question 13
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(13, 'sante', 'Problèmes de santé', 'fa-heartbeat', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":1, "droit":2, "enseignement":3, "agronomie":2, "communication":2}', 1),
(13, 'environnement', 'Problèmes environnementaux', 'fa-leaf', '{"informatique":2, "sante":2, "genie_civil":3, "gestion":2, "droit":2, "enseignement":3, "agronomie":5, "communication":3}', 2),
(13, 'education', "Problèmes d'éducation", 'fa-graduation-cap', '{"informatique":2, "sante":2, "genie_civil":1, "gestion":2, "droit":3, "enseignement":5, "agronomie":2, "communication":4}', 3),
(13, 'technologie', 'Défis technologiques', 'fa-microchip', '{"informatique":5, "sante":2, "genie_civil":4, "gestion":3, "droit":1, "enseignement":2, "agronomie":2, "communication":3}', 4);

-- Question 14
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(14, 'code', 'Code / Programmation', 'fa-code', '{"informatique":5, "sante":1, "genie_civil":2, "gestion":2, "droit":1, "enseignement":2, "agronomie":1, "communication":2}', 1),
(14, 'caeo', 'CAO / Design 3D', 'fa-cube', '{"informatique":3, "sante":1, "genie_civil":5, "gestion":1, "droit":1, "enseignement":2, "agronomie":3, "communication":2}', 2),
(14, 'bureautique', 'Bureautique / Gestion', 'fa-file-excel', '{"informatique":2, "sante":1, "genie_civil":2, "gestion":5, "droit":4, "enseignement":3, "agronomie":2, "communication":3}', 3),
(14, 'multimedia', 'Montage / Multimédia', 'fa-video', '{"informatique":3, "sante":1, "genie_civil":1, "gestion":2, "droit":1, "enseignement":3, "agronomie":1, "communication":5}', 4);

-- Question 15
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(15, 'tech', 'Tech / Numérique', 'fa-microchip', '{"informatique":5, "sante":1, "genie_civil":2, "gestion":3, "droit":1, "enseignement":2, "agronomie":1, "communication":3}', 1),
(15, 'sante', 'Santé / Bien-être', 'fa-heart', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":2, "droit":2, "enseignement":3, "agronomie":2, "communication":2}', 2),
(15, 'finance', 'Finance / Banque', 'fa-coins', '{"informatique":2, "sante":1, "genie_civil":1, "gestion":5, "droit":4, "enseignement":2, "agronomie":1, "communication":3}', 3),
(15, 'education', 'Éducation / Formation', 'fa-school', '{"informatique":2, "sante":2, "genie_civil":1, "gestion":2, "droit":3, "enseignement":5, "agronomie":2, "communication":3}', 4);

-- Question 16
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(16, 'visuel', 'Par la vue / Schémas', '{"informatique":4, "sante":3, "genie_civil":5, "gestion":3, "droit":3, "enseignement":4, "agronomie":4, "communication":3}', 1),
(16, 'auditif', "Par l'écoute / Cours", '{"informatique":2, "sante":3, "genie_civil":2, "gestion":3, "droit":4, "enseignement":5, "agronomie":2, "communication":4}', 2),
(16, 'pratique', 'Par la pratique / Exercices', '{"informatique":4, "sante":4, "genie_civil":4, "gestion":4, "droit":3, "enseignement":4, "agronomie":5, "communication":3}', 3);

-- Question 17
INSERT INTO quiz_options (question_id, option_valeur, option_texte, scores_domaines, ordre) VALUES
(17, 'leader', 'Leader / Meneur', '{"informatique":3, "sante":3, "genie_civil":4, "gestion":5, "droit":4, "enseignement":4, "agronomie":3, "communication":4}', 1),
(17, 'mediateur', 'Médiateur / Diplomate', '{"informatique":2, "sante":4, "genie_civil":2, "gestion":4, "droit":5, "enseignement":5, "agronomie":3, "communication":5}', 2),
(17, 'expert', 'Expert / Spécialiste', '{"informatique":5, "sante":4, "genie_civil":4, "gestion":3, "droit":4, "enseignement":3, "agronomie":4, "communication":2}', 3),
(17, 'support', 'Support / Aide', '{"informatique":2, "sante":5, "genie_civil":2, "gestion":3, "droit":3, "enseignement":4, "agronomie":3, "communication":3}', 4);

-- Question 18
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(18, 'hopital', 'À l\'hôpital', 'fa-hospital', '{"informatique":1, "sante":5, "genie_civil":1, "gestion":1, "droit":2, "enseignement":2, "agronomie":1, "communication":1}', 1),
(18, 'cabinet', 'En cabinet d\'avocats', 'fa-building', '{"informatique":1, "sante":1, "genie_civil":1, "gestion":3, "droit":5, "enseignement":2, "agronomie":1, "communication":3}', 2),
(18, 'startup', 'En startup tech', 'fa-rocket', '{"informatique":5, "sante":1, "genie_civil":2, "gestion":4, "droit":1, "enseignement":1, "agronomie":1, "communication":4}', 3),
(18, 'ecole', 'À l\'école', 'fa-school', '{"informatique":2, "sante":2, "genie_civil":1, "gestion":2, "droit":2, "enseignement":5, "agronomie":2, "communication":3}', 4);

-- Question 19
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(19, 'reussite', 'Réussite professionnelle', 'fa-trophy', '{"informatique":4, "sante":3, "genie_civil":4, "gestion":5, "droit":4, "enseignement":3, "agronomie":3, "communication":4}', 1),
(19, 'impact', 'Avoir un impact social', 'fa-hand-holding-heart', '{"informatique":2, "sante":5, "genie_civil":3, "gestion":3, "droit":4, "enseignement":5, "agronomie":4, "communication":4}', 2),
(19, 'innovation', 'Innover / Créer', 'fa-lightbulb', '{"informatique":5, "sante":2, "genie_civil":4, "gestion":4, "droit":2, "enseignement":3, "agronomie":3, "communication":4}', 3),
(19, 'stabilite', 'Stabilité financière', 'fa-shield', '{"informatique":3, "sante":4, "genie_civil":4, "gestion":5, "droit":5, "enseignement":4, "agronomie":4, "communication":3}', 4);

-- Question 20
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(20, 'expert', 'Expert reconnu', 'fa-award', '{"informatique":5, "sante":4, "genie_civil":5, "gestion":4, "droit":5, "enseignement":4, "agronomie":4, "communication":3}', 1),
(20, 'chef', 'Chef d\'entreprise', 'fa-chart-line', '{"informatique":4, "sante":2, "genie_civil":3, "gestion":5, "droit":3, "enseignement":2, "agronomie":3, "communication":4}', 2),
(20, 'fonctionnaire', 'Fonctionnaire', 'fa-building', '{"informatique":2, "sante":4, "genie_civil":4, "gestion":3, "droit":5, "enseignement":5, "agronomie":4, "communication":3}', 3),
(20, 'freelance', 'Freelance / Nomade', 'fa-globe', '{"informatique":4, "sante":2, "genie_civil":2, "gestion":4, "droit":2, "enseignement":2, "agronomie":2, "communication":5}', 4);

-- Question 21 (plaisir)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(21, 'lecture', 'Lecture / Écriture', 'fa-book', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 1),
(21, 'sport', 'Sport / Fitness', 'fa-person-running', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 2),
(21, 'jeux', 'Jeux vidéo / Tech', 'fa-gamepad', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 3),
(21, 'art', 'Art / Musique', 'fa-palette', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 4);

-- Question 22 (plaisir)
INSERT INTO quiz_options (question_id, option_valeur, option_texte, option_icone, scores_domaines, ordre) VALUES
(22, 'aventure', 'Aventure / Trek', 'fa-mountain', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 1),
(22, 'plage', 'Plage / Détente', 'fa-umbrella-beach', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 2),
(22, 'culture', 'Culture / Visites', 'fa-landmark', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 3),
(22, 'ville', 'Ville / Shopping', 'fa-city', '{"informatique":0, "sante":0, "genie_civil":0, "gestion":0, "droit":0, "enseignement":0, "agronomie":0, "communication":0}', 4);

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================