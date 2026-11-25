import { submitLogin } from '../../lib/api/auth';
import { ApiError } from '../../lib/api/client';
import { es } from '../../lib/i18n/es';

export function initLoginForm(): void {
  const form = document.getElementById('login-form');
  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');
  const toggleBtn = document.getElementById('toggle-password');
  const submitBtn = document.getElementById('login-submit');
  const errorBox = document.getElementById('login-error');

  if (
    !(emailInput instanceof HTMLInputElement) ||
    !(passwordInput instanceof HTMLInputElement) ||
    !(submitBtn instanceof HTMLButtonElement) ||
    !(errorBox instanceof HTMLParagraphElement)
  ) {
    return;
  }

  const setError = (message: string | null): void => {
    if (message === null) {
      errorBox.textContent = '';
      errorBox.hidden = true;
      emailInput.removeAttribute('aria-invalid');
      passwordInput.removeAttribute('aria-invalid');
      return;
    }
    errorBox.textContent = message;
    errorBox.hidden = false;
  };

  if (toggleBtn instanceof HTMLButtonElement) {
    toggleBtn.addEventListener('click', () => {
      const showing = passwordInput.type === 'text';
      passwordInput.type = showing ? 'password' : 'text';
      toggleBtn.setAttribute('aria-pressed', showing ? 'false' : 'true');
      toggleBtn.textContent = showing
        ? es.auth.showPassword
        : es.auth.hidePassword;
    });
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    setError(null);

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    if (!email.includes('@')) {
      emailInput.setAttribute('aria-invalid', 'true');
      setError(es.auth.validationEmail);
      emailInput.focus();
      return;
    }

    if (password.length === 0) {
      passwordInput.setAttribute('aria-invalid', 'true');
      setError(es.auth.validationPassword);
      passwordInput.focus();
      return;
    }

    void (async () => {
      submitBtn.disabled = true;
      submitBtn.textContent = es.auth.submittingButton;

      try {
        await submitLogin({ email, password });
        window.location.assign('/');
      } catch (err) {
        if (err instanceof ApiError) {
          setError(
            err.status === 401 ? es.auth.invalidCredentials : err.message,
          );
        } else {
          setError(es.auth.unexpectedError);
        }
        submitBtn.disabled = false;
        submitBtn.textContent = es.auth.submitButton;
      }
    })();
  });
}
