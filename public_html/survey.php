<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once __DIR__ . "/includes/gtag.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Survey - Oregon Tires Auto Care</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/favicon.ico" sizes="any">
    <link rel="icon" href="/assets/favicon.png" type="image/png" sizes="32x32">
    <link rel="stylesheet" href="/assets/styles.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
    <style>
    .star-btn { cursor: pointer; transition: transform 0.15s, color 0.15s; font-size: 2rem; }
    .star-btn:hover { transform: scale(1.2); }
    .star-btn.active { color: #f59e0b; }
    .star-btn.inactive { color: #d1d5db; }
    .dark .star-btn.inactive { color: #4b5563; }
    .nps-btn { width: 40px; height: 40px; border-radius: 8px; border: 2px solid #d1d5db; cursor: pointer; font-weight: bold; transition: all 0.15s; }
    .nps-btn:hover { border-color: #15803d; }
    .nps-btn.active { background: #15803d; color: white; border-color: #15803d; }
    .dark .nps-btn { border-color: #4b5563; color: #d1d5db; }
    .dark .nps-btn.active { background: #22c55e; border-color: #22c55e; color: #1a1a2e; }
    </style>
</head>
<body class="bg-green-50 dark:bg-gray-900 min-h-screen">
    <header class="bg-gradient-to-r from-green-700 via-green-800 to-gray-900 text-white py-6">
        <div class="max-w-2xl mx-auto px-4 text-center">
            <img src="/assets/logo.png" alt="Oregon Tires Auto Care" class="h-16 mx-auto mb-3">
            <h1 id="survey-title" class="text-2xl font-bold">Customer Survey</h1>
            <p id="survey-desc" class="text-green-200 text-sm mt-1"></p>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
        <div id="loading" class="text-center py-12">
            <div class="inline-block w-8 h-8 border-4 border-green-300 border-t-green-700 rounded-full animate-spin"></div>
            <p class="text-gray-500 mt-3">Loading survey...</p>
        </div>

        <div id="survey-form" class="hidden space-y-6"></div>

        <div id="submit-section" class="hidden text-center mt-8">
            <button onclick="submitSurvey()" id="submit-btn" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-3 rounded-xl font-bold text-lg hover:from-green-700 hover:to-green-800 transition shadow-lg">
                <span id="submit-text">Submit Survey</span>
            </button>
        </div>

        <div id="thank-you" class="hidden text-center py-12">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold text-green-700 dark:text-green-400 mb-2">Thank You!</h2>
            <p class="text-gray-600 dark:text-gray-300" id="thank-you-text">Your feedback helps us improve our service.</p>
            <p class="text-gray-500 dark:text-gray-400 mt-1" id="thank-you-text-es">Sus comentarios nos ayudan a mejorar nuestro servicio.</p>
            <a href="/" class="inline-block mt-6 bg-green-700 text-white px-6 py-2 rounded-lg hover:bg-green-800 transition">Visit Oregon Tires</a>
        </div>

        <div id="error-msg" class="hidden bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-6 text-center">
            <p class="text-red-700 dark:text-red-300 font-medium" id="error-text"></p>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 text-center py-4 text-sm mt-auto">
        Oregon Tires Auto Care | (503) 367-9714
    </footer>

<script>
var answers = {};
var surveyToken = '';
var lang = localStorage.getItem('lang') || 'en';

(function() {
    var params = new URLSearchParams(window.location.search);
    surveyToken = params.get('token') || '';

    if (!surveyToken) {
        // Try path-based: /survey/TOKEN
        var parts = window.location.pathname.split('/');
        if (parts.length >= 3 && parts[1] === 'survey') {
            surveyToken = parts[2];
        }
    }

    if (!surveyToken) {
        showError('Invalid survey link.');
        return;
    }

    fetch('/api/survey.php?token=' + encodeURIComponent(surveyToken))
        .then(function(r) { return r.json(); })
        .then(function(res) {
            document.getElementById('loading').classList.add('hidden');

            if (!res.success) {
                showError(res.error || 'Survey not found.');
                return;
            }

            if (res.data.completed) {
                document.getElementById('thank-you').classList.remove('hidden');
                document.getElementById('survey-title').textContent = lang === 'es' ? (res.data.title_es || res.data.title_en) : res.data.title_en;
                return;
            }

            // Set title
            document.getElementById('survey-title').textContent = lang === 'es' ? (res.data.title_es || res.data.title_en) : res.data.title_en;
            if (res.data.description_en || res.data.description_es) {
                document.getElementById('survey-desc').textContent = lang === 'es' ? (res.data.description_es || res.data.description_en) : res.data.description_en;
            }

            renderQuestions(res.data.questions);
        })
        .catch(function() {
            document.getElementById('loading').classList.add('hidden');
            showError('Failed to load survey. Please try again later.');
        });
})();

function renderQuestions(questions) {
    var form = document.getElementById('survey-form');
    form.innerHTML = '';

    questions.forEach(function(q, i) {
        var card = document.createElement('div');
        card.className = 'bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-100 dark:border-gray-700';

        var qText = lang === 'es' && q.question_es ? q.question_es : q.question_en;
        var required = q.is_required == 1 ? ' <span class="text-red-500">*</span>' : '';

        var html = '<h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">' + escapeHtml(qText) + required + '</h3>';

        if (q.question_type === 'rating') {
            html += '<div class="flex gap-2 justify-center" data-qid="' + q.id + '">';
            for (var s = 1; s <= 5; s++) {
                html += '<button type="button" class="star-btn inactive" data-val="' + s + '" onclick="selectRating(' + q.id + ',' + s + ')">★</button>';
            }
            html += '</div>';
            html += '<div class="flex justify-between text-xs text-gray-400 mt-1 px-2"><span>' + (lang === 'es' ? 'Malo' : 'Poor') + '</span><span>' + (lang === 'es' ? 'Excelente' : 'Excellent') + '</span></div>';
        }
        else if (q.question_type === 'nps') {
            html += '<div class="flex flex-wrap gap-2 justify-center" data-qid="' + q.id + '">';
            for (var n = 0; n <= 10; n++) {
                html += '<button type="button" class="nps-btn" data-val="' + n + '" onclick="selectNps(' + q.id + ',' + n + ')">' + n + '</button>';
            }
            html += '</div>';
            html += '<div class="flex justify-between text-xs text-gray-400 mt-2"><span>' + (lang === 'es' ? 'Nada probable' : 'Not at all likely') + '</span><span>' + (lang === 'es' ? 'Muy probable' : 'Extremely likely') + '</span></div>';
        }
        else if (q.question_type === 'text') {
            html += '<textarea id="text-' + q.id + '" rows="3" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="' + (lang === 'es' ? 'Escriba sus comentarios aquí...' : 'Type your feedback here...') + '" oninput="answers[' + q.id + ']=this.value"></textarea>';
        }
        else if (q.question_type === 'multiple_choice' && q.options) {
            html += '<div class="space-y-2" data-qid="' + q.id + '">';
            q.options.forEach(function(opt) {
                html += '<label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-green-400 cursor-pointer transition"><input type="radio" name="mc-' + q.id + '" value="' + escapeHtml(opt) + '" onchange="answers[' + q.id + ']=this.value" class="text-green-600"> <span class="text-gray-700 dark:text-gray-300">' + escapeHtml(opt) + '</span></label>';
            });
            html += '</div>';
        }

        card.innerHTML = html;
        form.appendChild(card);
    });

    form.classList.remove('hidden');
    document.getElementById('submit-section').classList.remove('hidden');
}

function selectRating(qId, val) {
    answers[qId] = val;
    var container = document.querySelector('[data-qid="' + qId + '"]');
    container.querySelectorAll('.star-btn').forEach(function(btn) {
        var v = parseInt(btn.getAttribute('data-val'));
        btn.classList.toggle('active', v <= val);
        btn.classList.toggle('inactive', v > val);
    });
}

function selectNps(qId, val) {
    answers[qId] = val;
    var container = document.querySelector('[data-qid="' + qId + '"]');
    container.querySelectorAll('.nps-btn').forEach(function(btn) {
        btn.classList.toggle('active', parseInt(btn.getAttribute('data-val')) === val);
    });
}

function submitSurvey() {
    var btn = document.getElementById('submit-btn');
    btn.disabled = true;
    document.getElementById('submit-text').textContent = lang === 'es' ? 'Enviando...' : 'Submitting...';

    fetch('/api/survey.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ token: surveyToken, answers: answers })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            document.getElementById('survey-form').classList.add('hidden');
            document.getElementById('submit-section').classList.add('hidden');
            document.getElementById('thank-you').classList.remove('hidden');
        } else {
            btn.disabled = false;
            document.getElementById('submit-text').textContent = lang === 'es' ? 'Enviar Encuesta' : 'Submit Survey';
            alert(res.error || 'Failed to submit. Please try again.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        document.getElementById('submit-text').textContent = lang === 'es' ? 'Enviar Encuesta' : 'Submit Survey';
        alert('Network error. Please try again.');
    });
}

function showError(msg) {
    document.getElementById('loading').classList.add('hidden');
    document.getElementById('error-text').textContent = msg;
    document.getElementById('error-msg').classList.remove('hidden');
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>
</body>
</html>
