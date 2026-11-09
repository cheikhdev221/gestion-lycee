document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-password-toggle]');
    const password = document.querySelector('#password');

    if (!toggle || !password) {
        return;
    }

    toggle.addEventListener('click', () => {
        const shouldShow = password.type === 'password';
        password.type = shouldShow ? 'text' : 'password';
        toggle.textContent = shouldShow ? 'Masquer' : 'Afficher';
        toggle.setAttribute('aria-label', shouldShow ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });
});
