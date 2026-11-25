import {
  createWaiter,
  deleteWaiter,
  updateWaiter,
} from '../../lib/api/waiters';
import { ApiError } from '../../lib/api/client';
import { es } from '../../lib/i18n/es';

function installLightDismissFallback(dialogs: HTMLDialogElement[]): void {
  if ('closedBy' in HTMLDialogElement.prototype) {
    return;
  }

  const isOutside = (
    event: PointerEvent,
    dialog: HTMLDialogElement,
  ): boolean => {
    const rect = dialog.getBoundingClientRect();
    return (
      event.clientX < rect.left ||
      event.clientX > rect.right ||
      event.clientY < rect.top ||
      event.clientY > rect.bottom
    );
  };

  for (const dialog of dialogs) {
    let pressedOutside = false;
    dialog.addEventListener('pointerdown', (event) => {
      pressedOutside = isOutside(event, dialog);
    });
    dialog.addEventListener('pointerup', (event) => {
      if (pressedOutside && isOutside(event, dialog)) {
        dialog.close();
      }
      pressedOutside = false;
    });
  }
}

export function initWaitersManager(): void {
  const formDialog = document.getElementById('waiter-form-dialog');
  const deleteDialog = document.getElementById('waiter-delete-dialog');

  if (
    !(formDialog instanceof HTMLDialogElement) ||
    !(deleteDialog instanceof HTMLDialogElement)
  ) {
    return;
  }

  installLightDismissFallback([formDialog, deleteDialog]);

  const formTitle = document.getElementById('waiter-form-title');
  const waiterForm = document.getElementById('waiter-form');
  const nameInput = document.getElementById('waiter-name');
  const emailInput = document.getElementById('waiter-email');
  const activeInput = document.getElementById('waiter-active');
  const formError = document.getElementById('waiter-form-error');
  const submitButton = document.getElementById('waiter-submit-btn');

  const deleteWarning = document.getElementById('waiter-delete-warning');
  const deleteError = document.getElementById('waiter-delete-error');
  const confirmDeleteButton = document.getElementById(
    'waiter-confirm-delete-btn',
  );

  if (
    !(formTitle instanceof HTMLElement) ||
    !(waiterForm instanceof HTMLFormElement) ||
    !(nameInput instanceof HTMLInputElement) ||
    !(emailInput instanceof HTMLInputElement) ||
    !(activeInput instanceof HTMLInputElement) ||
    !(formError instanceof HTMLElement) ||
    !(submitButton instanceof HTMLButtonElement) ||
    !(deleteWarning instanceof HTMLElement) ||
    !(deleteError instanceof HTMLElement) ||
    !(confirmDeleteButton instanceof HTMLButtonElement)
  ) {
    return;
  }

  let editingWaiterId: number | null = null;
  let deletingWaiterId: number | null = null;

  const setFormError = (
    message: string | null,
    invalidControl?: HTMLElement,
  ): void => {
    for (const control of [nameInput, emailInput]) {
      control.removeAttribute('aria-invalid');
    }

    if (message === null) {
      formError.textContent = '';
      formError.hidden = true;
      return;
    }

    formError.textContent = message;
    formError.hidden = false;
    invalidControl?.setAttribute('aria-invalid', 'true');
  };

  const openFormModal = (
    mode: 'create' | 'edit',
    waiter?: { id: number; name: string; email: string; isActive: boolean },
  ): void => {
    editingWaiterId = mode === 'edit' && waiter ? waiter.id : null;
    formTitle.textContent =
      mode === 'edit' ? es.waiters.modalEditTitle : es.waiters.modalCreateTitle;
    nameInput.value = waiter?.name ?? '';
    emailInput.value = waiter?.email ?? '';
    activeInput.checked = waiter ? waiter.isActive : true;

    setFormError(null);
    submitButton.disabled = false;
    submitButton.textContent = es.waiters.saveButton;
    formDialog.showModal();
    nameInput.focus();
  };

  const openDeleteModal = (id: number, name: string): void => {
    deletingWaiterId = id;
    deleteWarning.textContent = es.waiters.deleteWarning(name);
    deleteError.textContent = '';
    deleteError.hidden = true;
    confirmDeleteButton.disabled = false;
    confirmDeleteButton.textContent = es.waiters.confirmDeleteButton;
    deleteDialog.showModal();
  };

  document
    .getElementById('open-create-waiter')
    ?.addEventListener('click', () => {
      openFormModal('create');
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="edit-waiter"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['waiterId']);
        if (!Number.isNaN(id) && id > 0) {
          openFormModal('edit', {
            id,
            name: btn.dataset['waiterName'] ?? '',
            email: btn.dataset['waiterEmail'] ?? '',
            isActive: btn.dataset['waiterActive'] !== 'false',
          });
        }
      });
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="delete-waiter"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['waiterId']);
        const name = btn.dataset['waiterName'] ?? '';
        if (!Number.isNaN(id) && id > 0) {
          openDeleteModal(id, name);
        }
      });
    });

  for (const control of [nameInput, emailInput]) {
    control.addEventListener('input', () => {
      if (control.getAttribute('aria-invalid') === 'true') {
        setFormError(null);
      }
    });
  }

  formDialog.addEventListener('close', () => {
    formDialog.returnValue = '';
  });

  deleteDialog.addEventListener('close', () => {
    deleteDialog.returnValue = '';
  });

  waiterForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();

    if (name.length < 2 || name.length > 150) {
      setFormError(es.waiters.validationName, nameInput);
      nameInput.focus();
      return;
    }

    if (!emailInput.checkValidity() || email.length === 0) {
      setFormError(es.waiters.validationEmail, emailInput);
      emailInput.focus();
      return;
    }

    submitButton.disabled = true;
    submitButton.textContent = es.waiters.savingButton;
    setFormError(null);

    const payload = { name, email, is_active: activeInput.checked };

    try {
      if (editingWaiterId !== null) {
        await updateWaiter(editingWaiterId, payload);
      } else {
        await createWaiter(payload);
      }
      formDialog.close();
      window.location.reload();
    } catch (error) {
      let message: string = es.waiters.unexpectedError;
      if (error instanceof ApiError) {
        const firstFieldError = error.errors
          ? Object.values(error.errors).flat()[0]
          : undefined;
        message = firstFieldError ?? error.message;
      }
      setFormError(message);
      submitButton.disabled = false;
      submitButton.textContent = es.waiters.saveButton;
    }
  });

  confirmDeleteButton.addEventListener('click', async () => {
    if (deletingWaiterId === null) {
      return;
    }

    confirmDeleteButton.disabled = true;
    confirmDeleteButton.textContent = es.waiters.deletingButton;
    deleteError.textContent = '';
    deleteError.hidden = true;

    try {
      await deleteWaiter(deletingWaiterId);
      deleteDialog.close();
      window.location.reload();
    } catch (error) {
      deleteError.textContent =
        error instanceof ApiError ? error.message : es.waiters.unexpectedError;
      deleteError.hidden = false;
      confirmDeleteButton.disabled = false;
      confirmDeleteButton.textContent = es.waiters.confirmDeleteButton;
    }
  });
}
