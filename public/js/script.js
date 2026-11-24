document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Delete this task?')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-status-form]').forEach((form) => {
    const status = form.querySelector('select');
    form.querySelector('.status-save').hidden = true;
    status.addEventListener('change', () => form.requestSubmit());
});

const password = document.querySelector('[name="password"]');
const confirmation = document.querySelector('[name="confirm_password"]');
if (password && confirmation) {
    const checkPasswords = () => {
        const mismatch = confirmation.value && password.value !== confirmation.value;
        confirmation.setCustomValidity(mismatch ? 'The passwords do not match.' : '');
    };
    password.addEventListener('input', checkPasswords);
    confirmation.addEventListener('input', checkPasswords);
}
