(function() {
    const STORAGE_KEY = 'x777x_theme_preference';
    const LIGHT_CLASS = 'x777x-light-mode';

    function checkTheme() {
        const saved = localStorage.getItem(STORAGE_KEY);
        // Если пользователь выбрал 'light', включаем класс
        if (saved === 'light') {
            document.body.classList.add(LIGHT_CLASS);
        } else {
            document.body.classList.remove(LIGHT_CLASS);
        }
    }

    // Запускаем немедленно
    checkTheme();

    document.addEventListener('DOMContentLoaded', function() {
        checkTheme();
        const btn = document.getElementById('x777x-theme-toggle-btn');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle(LIGHT_CLASS);
                
                if (document.body.classList.contains(LIGHT_CLASS)) {
                    localStorage.setItem(STORAGE_KEY, 'light');
                } else {
                    localStorage.setItem(STORAGE_KEY, 'dark');
                }
            });
        }
    });
})();