<?php
// pages/universites.php

session_start();
require_once '../php/db.php';

// Récupérer toutes les universités avec leurs filières
$stmt = $pdo->query("
    SELECT u.*, 
           f.id as filiere_id,
           f.nom as filiere_nom,
           f.description as filiere_description,
           d.nom as domaine_nom
    FROM universites u
    LEFT JOIN universites_filieres uf ON u.id = uf.universite_id
    LEFT JOIN filieres f ON uf.filiere_id = f.id
    LEFT JOIN domaines d ON f.domaine_id = d.id
    WHERE u.actif = 1
    ORDER BY u.pays, u.nom, f.nom
");
$results = $stmt->fetchAll();

// Organiser les données par université
$universites_data = [];
foreach ($results as $row) {
    $univ_id = $row['id'];
    if (!isset($universites_data[$univ_id])) {
        $universites_data[$univ_id] = [
            'id' => $row['id'],
            'nom' => $row['nom'],
            'description' => $row['description'] ?? 'Université partenaire',
            'localisation' => ($row['ville'] ? $row['ville'] . ', ' : '') . $row['pays'],
            'pays' => $row['pays'],
            'ville' => $row['ville'],
            'site_web' => $row['site_web'],
            'logo' => $row['logo'] ?? null,
            'filieres' => []
        ];
    }
    if ($row['filiere_id']) {
        $universites_data[$univ_id]['filieres'][] = [
            'id' => $row['filiere_id'],
            'nom' => $row['filiere_nom'],
            'description' => $row['filiere_description'] ?? 'Formation disponible',
            'domaine' => $row['domaine_nom'] ?? ''
        ];
    }
}

// Convertir en tableau indexé
$universites = array_values($universites_data);

// Récupérer les domaines pour le filtre
$stmt = $pdo->query("SELECT id, nom FROM domaines ORDER BY nom");
$domaines = $stmt->fetchAll();

// Définir les images par défaut
$default_images = [
    'Université d\'Abomey-Calavi' => 'uac.jpg',
    'Université de Parakou' => 'parakou.jpg',
    'Université de Lomé' => 'lome.jpg',
    'Université de Kara' => 'kara.jpg',
    'ESGIS Bénin' => 'esgis.jpg',
    'EPITECH Bénin' => 'epitech.jpg',
    'HECM' => 'hecm.jpg',
    'Institut CERCO' => 'cerco.jpg',
    'University of Ghana' => 'ghana.jpg',
    'KNUST' => 'knust.jpg',
    'University of Lagos' => 'lagos.jpg',
    'Université Cheikh Anta Diop' => 'ucad.jpg',
    'Université Félix Houphouët-Boigny' => 'ufhb.jpg',
];

$page_prefix = '../';
include '../includes/header.php';
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
    
    <style>
        .filters-section {
            margin-bottom: 40px;
        }
        .filters {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .filter-input {
            padding: 12px 20px;
            border: 1px solid var(--border);
            border-radius: 30px;
            font-family: inherit;
            font-size: 14px;
            width: 250px;
            background: white;
        }
        .universites-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        .university-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: 0.3s;
        }
        .university-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(37,99,235,0.15);
        }
        .card-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-image .fallback-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 60px;
            color: rgba(255,255,255,0.3);
        }
        .card-image .univ-name-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px 20px 15px;
            background: linear-gradient(transparent, rgba(0,0,0,0.5));
        }
        .card-image .univ-name-overlay h3 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        .card-body {
            padding: 20px;
        }
        .card-body .localisation {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 12px;
        }
        .card-body .localisation i {
            color: var(--primary);
        }
        .filieres-tag {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 12px 0;
        }
        .filiere-tag {
            background: white;
            border: 2px solid var(--primary);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filiere-tag:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .no-results {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 24px;
            color: var(--gray);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 480px;
            width: 90%;
            position: relative;
            animation: modalFadeIn 0.2s ease;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            background: var(--primary);
            color: white;
            padding: 22px 26px;
            border-radius: 20px 20px 0 0;
        }
        .modal-header h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        .modal-header p {
            font-size: 14px;
            opacity: 0.85;
            margin: 0;
        }
        .modal-close {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: white;
            transition: 0.2s;
            background: rgba(255, 255, 255, 0.15);
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .modal-body {
            padding: 24px 26px;
            background: white;
            border-radius: 0 0 20px 20px;
        }
        .modal-body .info-row {
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #edf2f7;
        }
        .modal-body .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .modal-body .info-row h4 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-body .info-row h4 i {
            color: var(--primary);
            font-size: 13px;
        }
        .modal-body .info-row p {
            font-size: 15px;
            line-height: 1.5;
            color: #1e293b;
            margin: 0;
        }
        @media(max-width: 1100px) {
            .universites-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media(max-width: 768px) {
            .universites-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-left">
            <h1>Universités et <span>Filières</span></h1>
            <p class="hero-text">
                Découvrez les universités partenaires et leurs filières principales.
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
                <input type="text" id="searchInput" class="filter-input" placeholder="Rechercher une université...">
                <select id="domainFilter" class="filter-input">
                    <option value="">Tous les domaines</option>
                    <?php foreach ($domaines as $domaine): ?>
                        <option value="<?= htmlspecialchars($domaine['nom']) ?>"><?= htmlspecialchars($domaine['nom']) ?></option>
                    <?php endforeach; ?>
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
// DONNÉES DES UNIVERSITÉS DEPUIS PHP
const universitesData = <?= json_encode($universites) ?>;

// Images par défaut
const defaultImages = <?= json_encode($default_images) ?>;

function getImageUrl(univ) {
    if (univ.logo) {
        return '../assets/images/universites/' + univ.logo;
    }
    const imageName = defaultImages[univ.nom] || 'default.jpg';
    return '../assets/images/universites/' + imageName;
}

function renderUniversites(filterText = "", filterDomaine = "") {
    const container = document.getElementById("universitesGrid");
    
    const filteredUniv = universitesData.filter(univ => {
        const matchNom = univ.nom.toLowerCase().includes(filterText.toLowerCase());
        const matchPays = univ.pays.toLowerCase().includes(filterText.toLowerCase());
        const matchFiliere = filterDomaine === "" || univ.filieres.some(f => f.domaine === filterDomaine);
        return (matchNom || matchPays) && matchFiliere;
    });

    if (filteredUniv.length === 0) {
        container.innerHTML = '<div class="no-results">Aucune université trouvée</div>';
        return;
    }

    container.innerHTML = filteredUniv.map(univ => `
        <div class="university-card">
            <div class="card-image">
                <img src="${getImageUrl(univ)}" 
                     alt="${univ.nom}"
                     onerror="this.style.display='none'">
                <div class="fallback-icon" style="display:${univ.logo ? 'none' : 'block'}">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="univ-name-overlay">
                    <h3>${univ.nom}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="localisation">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>${univ.localisation}</span>
                </div>
                <div class="filieres-tag">
                    ${univ.filieres.slice(0, 6).map(filiere => `
                        <button class="filiere-tag" data-filiere='${JSON.stringify(filiere)}' data-univ="${univ.nom}">
                            <i class="fa-regular fa-eye"></i> ${filiere.nom}
                        </button>
                    `).join('')}
                    ${univ.filieres.length > 6 ? `<span style="font-size:12px;color:#94a3b8;">+${univ.filieres.length - 6}</span>` : ''}
                </div>
                ${univ.site_web ? `<div style="margin-top:8px;font-size:12px;color:#94a3b8;"><i class="fa-solid fa-globe"></i> <a href="${univ.site_web}" target="_blank" style="color:var(--primary);text-decoration:none;">${univ.site_web}</a></div>` : ''}
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

function openModal(filiere, universiteNom) {
    const modal = document.getElementById("filiereModal");
    document.getElementById("modalTitle").innerText = filiere.nom;
    document.getElementById("modalUniv").innerHTML = `<i class="fa-solid fa-building-columns"></i> ${universiteNom}`;
    
    const modalBody = document.getElementById("modalBody");
    modalBody.innerHTML = `
        <div class="info-row">
            <h4><i class="fa-solid fa-tag"></i> Domaine</h4>
            <p>${filiere.domaine || 'Non spécifié'}</p>
        </div>
        <div class="info-row">
            <h4><i class="fa-solid fa-align-left"></i> Description</h4>
            <p>${filiere.description || 'Formation disponible dans cette université.'}</p>
        </div>
        <div class="info-row">
            <h4><i class="fa-solid fa-building-columns"></i> Université</h4>
            <p>${universiteNom}</p>
        </div>
    `;
    
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    const modal = document.getElementById("filiereModal");
    modal.classList.remove("active");
    document.body.style.overflow = "auto";
}

window.onclick = function(event) {
    const modal = document.getElementById("filiereModal");
    if (event.target === modal) {
        closeModal();
    }
}

document.getElementById("searchInput").addEventListener("input", (e) => {
    renderUniversites(e.target.value, document.getElementById("domainFilter").value);
});

document.getElementById("domainFilter").addEventListener("change", (e) => {
    renderUniversites(document.getElementById("searchInput").value, e.target.value);
});

renderUniversites();
</script>

<script src="../js/script.js"></script>

</body>
</html>