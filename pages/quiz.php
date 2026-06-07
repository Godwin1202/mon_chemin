<?php
// pages/quiz.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'utilisateur a déjà un quiz en attente
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_sessions WHERE utilisateur_id = ? AND statut_validation = 'en_attente'");
    $stmt->execute([$user_id]);
    $has_pending = $stmt->fetchColumn();

    if ($has_pending > 0) {
        header('Location: attente.php');
        exit();
    }
} catch (PDOException $e) {
    // Table peut ne pas exister encore
}

// Récupérer toutes les questions du quiz
$questions = [];

try {
    $stmt = $pdo->query("SELECT * FROM quiz_questions WHERE actif = 1 ORDER BY numero");
    $questions = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la table n'existe pas, on utilise des données par défaut
}

// Si aucune question en base, utiliser des données par défaut
if (empty($questions)) {
    $questions = [
        ['id' => 1, 'numero' => 1, 'question_text' => 'Quelle matière préfères-tu ?', 'type_reponse' => 'carte'],
        ['id' => 2, 'numero' => 2, 'question_text' => 'Tu préfères ?', 'type_reponse' => 'radio'],
        ['id' => 3, 'numero' => 3, 'question_text' => 'Quel environnement de travail t\'attire le plus ?', 'type_reponse' => 'carte'],
        ['id' => 4, 'numero' => 4, 'question_text' => 'Pendant ton temps libre, tu préfères ?', 'type_reponse' => 'radio'],
        ['id' => 5, 'numero' => 5, 'question_text' => 'Tu es plus ?', 'type_reponse' => 'radio'],
        ['id' => 6, 'numero' => 6, 'question_text' => 'Tu aimerais travailler ?', 'type_reponse' => 'carte'],
        ['id' => 7, 'numero' => 7, 'question_text' => 'Quelle activité te plaît le plus ?', 'type_reponse' => 'carte'],
        ['id' => 8, 'numero' => 8, 'question_text' => 'Tu préfères ?', 'type_reponse' => 'radio'],
        ['id' => 9, 'numero' => 9, 'question_text' => 'Si tu crées un projet, tu es plutôt ?', 'type_reponse' => 'radio'],
        ['id' => 10, 'numero' => 10, 'question_text' => 'Ton point fort est ?', 'type_reponse' => 'carte'],
        ['id' => 11, 'numero' => 11, 'question_text' => 'Tu préfères travailler ?', 'type_reponse' => 'radio'],
        ['id' => 12, 'numero' => 12, 'question_text' => 'Quel métier t\'impressionne le plus ?', 'type_reponse' => 'carte'],
        ['id' => 13, 'numero' => 13, 'question_text' => 'Quel problème aimerais-tu résoudre ?', 'type_reponse' => 'carte'],
        ['id' => 14, 'numero' => 14, 'question_text' => 'Quel outil aimerais-tu maîtriser ?', 'type_reponse' => 'carte'],
        ['id' => 15, 'numero' => 15, 'question_text' => 'Quel secteur t\'intéresse le plus ?', 'type_reponse' => 'carte'],
        ['id' => 16, 'numero' => 16, 'question_text' => 'Tu préfères apprendre ?', 'type_reponse' => 'radio'],
        ['id' => 17, 'numero' => 17, 'question_text' => 'Dans un groupe, tu es plutôt ?', 'type_reponse' => 'radio'],
        ['id' => 18, 'numero' => 18, 'question_text' => 'Quel stage choisirais-tu ?', 'type_reponse' => 'carte'],
        ['id' => 19, 'numero' => 19, 'question_text' => 'Quel est ton objectif principal ?', 'type_reponse' => 'carte'],
        ['id' => 20, 'numero' => 20, 'question_text' => 'Ton futur idéal ressemble à ?', 'type_reponse' => 'carte'],
        ['id' => 21, 'numero' => 21, 'question_text' => 'Ton hobby préféré ?', 'type_reponse' => 'carte'],
        ['id' => 22, 'numero' => 22, 'question_text' => 'Ton rêve de vacances ?', 'type_reponse' => 'carte']
    ];
}

// Organiser les questions avec leurs options
$questions_json = [];
foreach ($questions as $q) {
    $options = [];
    
    try {
        $stmt_opts = $pdo->prepare("SELECT option_valeur, option_texte, option_icone FROM quiz_options WHERE question_id = ? ORDER BY ordre");
        $stmt_opts->execute([$q['id']]);
        $options = $stmt_opts->fetchAll();
    } catch (PDOException $e) {
        // Options par défaut
    }
    
    // Options par défaut si aucune en base
    if (empty($options)) {
        $options = getDefaultOptions($q['numero']);
    }
    
    $questions_json[] = [
        'numero' => $q['numero'],
        'texte' => $q['question_text'],
        'type' => $q['type_reponse'],
        'options' => $options
    ];
}

// Fonction pour les options par défaut
function getDefaultOptions($question_num) {
    $defaultOptions = [
        1 => [
            ['option_valeur' => 'maths', 'option_texte' => 'Mathématiques / Sciences', 'option_icone' => 'fa-calculator'],
            ['option_valeur' => 'informatique', 'option_texte' => 'Informatique / Programmation', 'option_icone' => 'fa-laptop-code'],
            ['option_valeur' => 'lettres', 'option_texte' => 'Lettres / Langues', 'option_icone' => 'fa-book'],
            ['option_valeur' => 'economie', 'option_texte' => 'Économie / Commerce', 'option_icone' => 'fa-chart-line']
        ],
        2 => [
            ['option_valeur' => 'theorique', 'option_texte' => 'Travail théorique / Réflexion', 'option_icone' => null],
            ['option_valeur' => 'pratique', 'option_texte' => 'Travail pratique / Manuel', 'option_icone' => null],
            ['option_valeur' => 'creatif', 'option_texte' => 'Travail créatif / Artistique', 'option_icone' => null]
        ],
        3 => [
            ['option_valeur' => 'bureau', 'option_texte' => 'Bureau / Calme', 'option_icone' => 'fa-building'],
            ['option_valeur' => 'terrain', 'option_texte' => 'Terrain / Extérieur', 'option_icone' => 'fa-tree'],
            ['option_valeur' => 'labo', 'option_texte' => 'Laboratoire / Recherche', 'option_icone' => 'fa-flask'],
            ['option_valeur' => 'hybride', 'option_texte' => 'Mixte / Flexible', 'option_icone' => 'fa-arrows-spin']
        ],
        4 => [
            ['option_valeur' => 'lire', 'option_texte' => 'Lire / Apprendre', 'option_icone' => null],
            ['option_valeur' => 'coder', 'option_texte' => 'Coder / Créer', 'option_icone' => null],
            ['option_valeur' => 'sport', 'option_texte' => 'Faire du sport', 'option_icone' => null],
            ['option_valeur' => 'sortir', 'option_texte' => 'Sortir avec des amis', 'option_icone' => null]
        ]
    ];
    
    return isset($defaultOptions[$question_num]) ? $defaultOptions[$question_num] : [
        ['option_valeur' => 'option1', 'option_texte' => 'Option 1', 'option_icone' => null],
        ['option_valeur' => 'option2', 'option_texte' => 'Option 2', 'option_icone' => null],
        ['option_valeur' => 'option3', 'option_texte' => 'Option 3', 'option_icone' => null]
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz d'orientation - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .quiz-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .progress-section {
            margin-bottom: 40px;
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 5px;
        }
        .step-indicator {
            text-align: center;
            flex: 1;
            font-size: 12px;
            color: #9ca3af;
        }
        .step-indicator.active {
            color: #2563eb;
            font-weight: 600;
        }
        .step-indicator.completed {
            color: #10b981;
        }
        .step-indicator .step-number {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            background: #e5e7eb;
            border-radius: 50%;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
        }
        .step-indicator.active .step-number {
            background: #2563eb;
            color: white;
        }
        .step-indicator.completed .step-number {
            background: #10b981;
            color: white;
        }
        .progress-bar-container {
            background: #e5e7eb;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }
        .question-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .question-text {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #1f2937;
        }
        .options-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .option-radio {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            cursor: pointer;
            transition: 0.2s;
        }
        .option-radio:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .option-radio.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .option-radio input {
            width: 20px;
            height: 20px;
            accent-color: #2563eb;
            cursor: pointer;
        }
        .option-radio label {
            flex: 1;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #374151;
        }
        .options-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .option-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }
        .option-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
        }
        .option-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .option-card i {
            font-size: 32px;
            color: #2563eb;
            margin-bottom: 12px;
            display: block;
        }
        .option-card span {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }
        .quiz-nav {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }
        .btn-nav {
            padding: 14px 28px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            font-size: 15px;
        }
        .btn-prev {
            background: white;
            border: 2px solid #2563eb;
            color: #2563eb;
        }
        .btn-prev:hover {
            background: #eff6ff;
            transform: translateY(-2px);
        }
        .btn-next {
            background: #2563eb;
            color: white;
        }
        .btn-next:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        .btn-submit {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .question-card { padding: 25px; }
            .question-text { font-size: 20px; }
            .options-cards { grid-template-columns: 1fr; }
            .step-indicator span:last-child { display: none; }
        }
    </style>
</head>
<body>

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
            <a href="quiz.php" class="active">Quiz</a>
            <a href="universites.php">Universités</a>
            <a href="conseils.php">Conseils</a>
            <a href="apropos.php">À propos</a>
        </nav>
        <div class="header-buttons">
            <a href="profil.php" class="btn white-btn">Mon profil</a>
            <a href="../php/auth.php?deconnexion=1" class="btn blue-btn">Déconnexion</a>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container hero-content">
        <div class="hero-left">
            <h1>Quiz d'<span>orientation</span></h1>
            <p class="hero-text">
                Répondez aux questions suivantes afin de nous aider à mieux comprendre 
                votre profil et vos centres d'intérêt.
            </p>
            <div class="hero-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <div>
                    <h3><?= count($questions_json) ?> questions</h3>
                    <p>Comptez environ 10 minutes pour compléter le questionnaire.</p>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <img src="../assets/illustrations/hero1.svg" alt="Quiz">
        </div>
    </div>
</section>

<div class="quiz-container" id="quizContainer">
    <div class="progress-section">
        <div class="progress-steps" id="progressSteps"></div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" id="progressBar"></div>
        </div>
    </div>

    <form id="quizForm" method="POST" action="traiter_quiz.php">
        <div id="questionsContainer"></div>
        
        <div class="quiz-nav">
            <button type="button" class="btn-nav btn-prev" id="prevBtn" style="visibility: hidden;">← Précédent</button>
            <button type="button" class="btn-nav btn-next" id="nextBtn">Suivant →</button>
            <button type="submit" class="btn-nav btn-submit" id="submitBtn" style="display: none;">✓ Soumettre le quiz</button>
        </div>
    </form>
</div>

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
        </div>
        <div>
            <h4>Mon compte</h4>
            <a href="connexion.php">Se connecter</a>
            <a href="inscription.php">S'inscrire</a>
            <a href="profil.php">Mon profil</a>
        </div>
    </div>
</footer>

<script>
// Données des questions depuis PHP
const questions = <?php echo json_encode($questions_json, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

console.log('Questions chargées :', questions.length);

let currentQuestion = 0;
let userAnswers = {};

function renderProgressSteps() {
    const container = document.getElementById('progressSteps');
    if (!container) return;
    const total = questions.length;
    container.innerHTML = '';
    
    questions.forEach((q, index) => {
        const step = document.createElement('div');
        step.className = 'step-indicator';
        if (index === currentQuestion) step.classList.add('active');
        else if (userAnswers[q.numero]) step.classList.add('completed');
        
        step.innerHTML = `
            <div class="step-number">${index + 1}</div>
            <span>Étape ${index + 1}/${total}</span>
        `;
        container.appendChild(step);
    });
    
    const progress = ((currentQuestion + 1) / total) * 100;
    const progressBar = document.getElementById('progressBar');
    if (progressBar) progressBar.style.width = `${progress}%`;
}

function renderQuestion(index) {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    
    const question = questions[index];
    if (!question) return;
    
    const savedValue = userAnswers[question.numero] || '';
    
    let optionsHtml = '';
    if (question.type === 'radio') {
        optionsHtml = '<div class="options-group">';
        question.options.forEach(opt => {
            const checked = savedValue === opt.option_valeur ? 'checked' : '';
            optionsHtml += `
                <div class="option-radio ${savedValue === opt.option_valeur ? 'selected' : ''}" onclick="this.querySelector('input').click()">
                    <input type="radio" name="q${question.numero}" value="${opt.option_valeur}" id="opt_${opt.option_valeur}" ${checked} onchange="saveAnswer(${question.numero}, this.value)">
                    <label for="opt_${opt.option_valeur}">${escapeHtml(opt.option_texte)}</label>
                </div>
            `;
        });
        optionsHtml += '</div>';
    } else {
        optionsHtml = '<div class="options-cards">';
        question.options.forEach(opt => {
            const selected = savedValue === opt.option_valeur ? 'selected' : '';
            optionsHtml += `
                <div class="option-card ${selected}" onclick="selectCard(${question.numero}, '${opt.option_valeur}')">
                    ${opt.option_icone ? `<i class="fa-solid ${opt.option_icone}"></i>` : '<i class="fa-solid fa-circle-question"></i>'}
                    <span>${escapeHtml(opt.option_texte)}</span>
                </div>
            `;
        });
        optionsHtml += '</div>';
    }
    
    container.innerHTML = `
        <div class="question-card">
            <div class="question-text">${question.numero}. ${escapeHtml(question.texte)}</div>
            ${optionsHtml}
        </div>
    `;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function selectCard(questionNum, value) {
    saveAnswer(questionNum, value);
    renderQuestion(currentQuestion);
}

function saveAnswer(questionNum, value) {
    userAnswers[questionNum] = value;
    renderProgressSteps();
}

function nextQuestion() {
    const currentQ = questions[currentQuestion];
    if (!userAnswers[currentQ.numero]) {
        showError('Veuillez répondre à la question avant de continuer.');
        return;
    }
    if (currentQuestion < questions.length - 1) {
        currentQuestion++;
        renderQuestion(currentQuestion);
        renderProgressSteps();
        updateButtons();
    }
}

function prevQuestion() {
    if (currentQuestion > 0) {
        currentQuestion--;
        renderQuestion(currentQuestion);
        renderProgressSteps();
        updateButtons();
    }
}

function updateButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    if (prevBtn) prevBtn.style.visibility = currentQuestion === 0 ? 'hidden' : 'visible';
    
    if (currentQuestion === questions.length - 1) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'block';
    } else {
        if (nextBtn) nextBtn.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
}

function showError(message) {
    let errorDiv = document.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        const quizContainer = document.getElementById('quizContainer');
        const quizForm = document.getElementById('quizForm');
        if (quizContainer && quizForm) {
            quizContainer.insertBefore(errorDiv, quizForm);
        }
    }
    errorDiv.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${message}`;
    setTimeout(() => {
        if (errorDiv) errorDiv.remove();
    }, 3000);
}

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, initialisation du quiz...');
    
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const quizForm = document.getElementById('quizForm');
    
    if (nextBtn) nextBtn.addEventListener('click', nextQuestion);
    if (prevBtn) prevBtn.addEventListener('click', prevQuestion);
    
    if (quizForm) {
        quizForm.addEventListener('submit', (e) => {
            const lastQ = questions[questions.length - 1];
            if (!userAnswers[lastQ.numero]) {
                e.preventDefault();
                showError('Veuillez répondre à toutes les questions avant de soumettre.');
                return;
            }
            
            for (const [qNum, answer] of Object.entries(userAnswers)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `q${qNum}`;
                input.value = answer;
                e.target.appendChild(input);
            }
        });
    }
    
    renderProgressSteps();
    renderQuestion(currentQuestion);
    updateButtons();
});
</script>

</body>
</html>