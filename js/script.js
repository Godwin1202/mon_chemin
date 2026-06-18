// =========================
// THEME TOGGLE
// =========================

document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (icon) icon.className = 'fa-solid fa-sun';
        }
        
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            
            if (document.body.classList.contains('dark-mode')) {
                if (icon) icon.className = 'fa-solid fa-sun';
                localStorage.setItem('theme', 'dark');
            } else {
                if (icon) icon.className = 'fa-solid fa-moon';
                localStorage.setItem('theme', 'light');
            }
        });
    }
});