<?php
// pages/universites.php

session_start();

$is_connected = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? 'eleve';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universités - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- HEADER DYNAMIQUE -->
<header class="header">
    <div class="container header-content">
        <div class="logo">
            <a href="../index.php" style="display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit;">
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <h2>Mon Chemin</h2>
                    <p>Aide à l'orientation scolaire</p>
                </div>
            </a>
        </div>

        <nav class="navbar">
            <a href="../index.php">Accueil</a>
            <a href="quiz.php">Quiz</a>
            <a href="universites.php" class="active">Universités</a>
            <a href="conseils.php">Conseils</a>
            <a href="apropos.php">À propos</a>
        </nav>

        <div class="header-buttons">
            <?php if ($is_connected): ?>
                <div class="user-menu">
                    <a href="profil.php" class="btn white-btn">
                        <i class="fa-solid fa-user"></i> <span>Mon profil</span>
                    </a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="../admin/dashboard.php" class="btn blue-btn">
                            <i class="fa-solid fa-gauge-high"></i> <span>Admin</span>
                        </a>
                    <?php endif; ?>
                    <a href="../php/deconnexion.php" class="btn logout-btn">
                        <i class="fa-solid fa-sign-out-alt"></i> <span>Déconnexion</span>
                    </a>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="btn white-btn">Se connecter</a>
                <a href="inscription.php" class="btn blue-btn">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-left">
            <h1>Universités et <span>Filières</span></h1>
            <p class="hero-text">
                Découvrez les universités partenaires et les filières disponibles.
                Cliquez sur une filière pour obtenir toutes les informations.
            </p>
        </div>
        <div class="hero-right">
            <img src="../assets/illustrations/universite.svg" alt="Universités">
        </div>
    </div>
</section>

<!-- CONTENU PRINCIPAL -->
<section class="universites-section">
    <div class="container">
        <div class="filters-section">
            <div class="filters">
                <input type="text" id="searchInput" class="filter-input" placeholder="🔍 Rechercher une université...">
                <select id="domainFilter" class="filter-input">
                    <option value="">📚 Tous les domaines</option>
                    <option value="Sciences">🔬 Sciences</option>
                    <option value="Lettres">📖 Lettres / Sciences humaines</option>
                    <option value="Économie">💰 Économie / Gestion</option>
                    <option value="Informatique">💻 Informatique / Tech</option>
                    <option value="Droit">⚖️ Droit / Politique</option>
                    <option value="Santé">🏥 Santé / Médecine</option>
                </select>
            </div>
        </div>

        <div id="universitesGrid" class="universites-grid">
            <p style="text-align:center; grid-column:1/-1; padding:60px;">Chargement des universités...</p>
        </div>
    </div>
</section>

<!-- MODAL -->
<div id="filiereModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <h2 id="modalTitle">Titre</h2>
            <p id="modalUniv">Université</p>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<!-- CTA -->
<section class="cta-section">
    <div class="container cta-content">
        <div>
            <h2>Prêt à trouver votre voie ?</h2>
            <p>Commencez dès maintenant votre orientation personnalisée.</p>
            <a href="quiz.php" class="cta-btn">Commencer le quiz</a>
        </div>
        <img src="../assets/illustrations/student1.svg" alt="Quiz">
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container footer-grid">
        <div>
            <h3>Mon Chemin</h3>
            <p>Votre partenaire pour une orientation scolaire moderne.</p>
        </div>
        <div>
            <h4>Liens utiles</h4>
            <a href="quiz.php">Quiz</a>
            <a href="universites.php">Universités</a>
            <a href="conseils.php">Conseils</a>
        </div>
        <div>
            <h4>Informations</h4>
            <a href="#">FAQ</a>
            <a href="#">Contact</a>
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
        </div>
        <div>
            <h4>Réseaux sociaux</h4>
            <div class="socials">
                <i class="fab fa-facebook"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-linkedin"></i>
            </div>
        </div>
    </div>
</footer>

<script>
// DONNÉES DES UNIVERSITÉS ET FILIÈRES
const universitesData = [
    {
        id: 1,
        nom: "Université d'Abomey-Calavi",
        description: "La plus grande université du Bénin, offrant une large gamme de formations.",
        localisation: "Abomey-Calavi, Bénin",
        filieres: [
            {
                nom: "Informatique",
                domaine: "Informatique",
                description: "Formation aux métiers du développement, de la data et de la cybersécurité.",
                debouches: "Développeur, Data Scientist, Administrateur réseau, Consultant IT",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 150 000 FCFA/an"
            },
            {
                nom: "Mathématiques",
                domaine: "Sciences",
                description: "Formation théorique et appliquée en mathématiques.",
                debouches: "Enseignant, Statisticien, Actuaire, Chercheur",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 120 000 FCFA/an"
            },
            {
                nom: "Droit",
                domaine: "Droit",
                description: "Formation en droit public et privé.",
                debouches: "Avocat, Juriste d'entreprise, Notaire, Magistrat",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 150 000 FCFA/an"
            }
        ]
    },
    {
        id: 2,
        nom: "Université de Parakou",
        description: "Université de référence du nord du Bénin, spécialisée en sciences agronomiques.",
        localisation: "Parakou, Bénin",
        filieres: [
            {
                nom: "Agronomie",
                domaine: "Sciences",
                description: "Formation en agriculture durable et gestion des ressources naturelles.",
                debouches: "Ingénieur agronome, Chef de projet agricole, Consultant",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 120 000 FCFA/an"
            },
            {
                nom: "Économie",
                domaine: "Économie",
                description: "Formation en économie générale et gestion.",
                debouches: "Économiste, Analyste financier, Chargé d'études",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 130 000 FCFA/an"
            }
        ]
    },
    {
        id: 3,
        nom: "Université de Lomé",
        description: "Première université publique du Togo, offrant des formations variées.",
        localisation: "Lomé, Togo",
        filieres: [
            {
                nom: "Médecine",
                domaine: "Santé",
                description: "Formation complète en médecine générale et spécialisée.",
                debouches: "Médecin généraliste, Spécialiste, Chercheur médical",
                duree: "7 ans",
                cout: "Environ 500 000 FCFA/an"
            },
            {
                nom: "Gestion",
                domaine: "Économie",
                description: "Formation en management et gestion d'entreprise.",
                debouches: "Chef d'entreprise, Consultant, Manager",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 200 000 FCFA/an"
            }
        ]
    },
    {
        id: 4,
        nom: "Université de Kara",
        description: "Deuxième université publique du Togo, réputée pour ses formations en lettres.",
        localisation: "Kara, Togo",
        filieres: [
            {
                nom: "Lettres modernes",
                domaine: "Lettres",
                description: "Formation en littérature, linguistique et communication.",
                debouches: "Enseignant, Journaliste, Écrivain, Chargé de communication",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 120 000 FCFA/an"
            },
            {
                nom: "Histoire",
                domaine: "Lettres",
                description: "Formation en histoire et patrimoine.",
                debouches: "Enseignant, Chercheur, Archiviste, Guide touristique",
                duree: "3 ans (Licence) / 5 ans (Master)",
                cout: "Environ 120 000 FCFA/an"
            }
        ]
    }
];

// Rendu des universités en cartes
function renderUniversites(filterText = "", filterDomaine = "") {
    const container = document.getElementById("universitesGrid");
    
    const filteredUniv = universitesData.filter(univ => {
        const matchNom = univ.nom.toLowerCase().includes(filterText.toLowerCase());
        const matchFiliere = filterDomaine === "" || univ.filieres.some(f => f.domaine === filterDomaine);
        return matchNom && matchFiliere;
    });

    if (filteredUniv.length === 0) {
        container.innerHTML = '<div class="no-results">🔍 Aucune université trouvée</div>';
        return;
    }

    container.innerHTML = filteredUniv.map(univ => `
        <div class="university-card">
            <div class="card-header">
                <i class="fa-solid fa-building-columns"></i>
                <h3>${univ.nom}</h3>
                <p>${univ.description.substring(0, 70)}...</p>
            </div>
            <div class="card-body">
                <div class="filieres-tag">
                    ${univ.filieres.map(filiere => `
                        <button class="filiere-tag" data-filiere='${JSON.stringify(filiere)}' data-univ="${univ.nom}">
                            <i class="fa-regular fa-eye"></i> ${filiere.nom}
                        </button>
                    `).join('')}
                </div>
                <div class="localisation">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>${univ.localisation}</span>
                </div>
            </div>
        </div>
    `).join('');

    document.querySelectorAll('.filiere-tag').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const filiereData = JSON.parse(button.getAttribute('data-filiere'));
            const univNom = button.getAttribute('data-univ');
            openModal(filiereData, univNom);
        });
    });
}

// Ouvrir le modal
function openModal(filiere, universiteNom) {
    const modal = document.getElementById("filiereModal");
    document.getElementById("modalTitle").innerText = filiere.nom;
    document.getElementById("modalUniv").innerHTML = `<i class="fa-solid fa-building-columns"></i> ${universiteNom}`;
    
    const modalBody = document.getElementById("modalBody");
    modalBody.innerHTML = `
        <div class="info-row">
            <h4><i class="fa-solid fa-tag"></i> Domaine</h4>
            <p>${filiere.domaine}</p>
        </div>
        <div class="info-row">
            <h4><i class="fa-solid fa-align-left"></i> Description</h4>
            <p>${filiere.description}</p>
        </div>
        <div class="info-row">
            <h4><i class="fa-solid fa-briefcase"></i> Débouchés</h4>
            <p>${filiere.debouches}</p>
        </div>
        <div class="info-row">
            <h4><i class="fa-solid fa-clock"></i> Durée des études</h4>
            <p>${filiere.duree}</p>
        </div>
        <div class="info-row">
            <h4><i class="fa-solid fa-money-bill-wave"></i> Frais approximatifs</h4>
            <p>${filiere.cout}</p>
        </div>
    `;
    
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

// Fermer le modal
function closeModal() {
    const modal = document.getElementById("filiereModal");
    modal.classList.remove("active");
    document.body.style.overflow = "auto";
}

// Fermer le modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById("filiereModal");
    if (event.target === modal) {
        closeModal();
    }
}

// Filtres
document.getElementById("searchInput").addEventListener("input", (e) => {
    renderUniversites(e.target.value, document.getElementById("domainFilter").value);
});

document.getElementById("domainFilter").addEventListener("change", (e) => {
    renderUniversites(document.getElementById("searchInput").value, e.target.value);
});

// Initialisation
renderUniversites();
</script>

<script src="../js/script.js"></script>
</body>
</html>