import {
  createCategory,
  deleteCategory,
  updateCategory,
} from '../../lib/api/categories';
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

export function initCategoriesManager(): void {
  const formDialog = document.getElementById('category-form-dialog');
  const deleteDialog = document.getElementById('category-delete-dialog');

  if (
    !(formDialog instanceof HTMLDialogElement) ||
    !(deleteDialog instanceof HTMLDialogElement)
  ) {
    return;
  }

  installLightDismissFallback([formDialog, deleteDialog]);

  const formTitle = document.getElementById('category-form-title');
  const categoryForm = document.getElementById('category-form');
  const nameInput = document.getElementById('category-name');
  const formError = document.getElementById('category-form-error');
  const submitButton = document.getElementById('category-submit-btn');

  const deleteWarning = document.getElementById('category-delete-warning');
  const deleteError = document.getElementById('category-delete-error');
  const confirmDeleteButton = document.getElementById(
    'category-confirm-delete-btn',
  );

  if (
    !(formTitle instanceof HTMLElement) ||
    !(categoryForm instanceof HTMLFormElement) ||
    !(nameInput instanceof HTMLInputElement) ||
    !(formError instanceof HTMLElement) ||
    !(submitButton instanceof HTMLButtonElement) ||
    !(deleteWarning instanceof HTMLElement) ||
    !(deleteError instanceof HTMLElement) ||
    !(confirmDeleteButton instanceof HTMLButtonElement)
  ) {
    return;
  }

  let editingCategoryId: number | null = null;
  let deletingCategoryId: number | null = null;

  const setFormError = (message: string | null): void => {
    if (message === null) {
      formError.textContent = '';
      formError.hidden = true;
      nameInput.removeAttribute('aria-invalid');
      return;
    }

    formError.textContent = message;
    formError.hidden = false;
    nameInput.setAttribute('aria-invalid', 'true');
  };

  const openFormModal = (
    mode: 'create' | 'edit',
    id?: number,
    name = '',
  ): void => {
    editingCategoryId = mode === 'edit' && id !== undefined ? id : null;
    formTitle.textContent =
      mode === 'edit'
        ? es.categories.modalEditTitle
        : es.categories.modalCreateTitle;
    nameInput.value = name;
    setFormError(null);
    submitButton.disabled = false;
    submitButton.textContent = es.categories.saveButton;
    formDialog.showModal();
    nameInput.focus();
  };

  const openDeleteModal = (id: number, name: string): void => {
    deletingCategoryId = id;
    deleteWarning.textContent = es.categories.deleteWarning(name);
    deleteError.textContent = '';
    deleteError.hidden = true;
    confirmDeleteButton.disabled = false;
    confirmDeleteButton.textContent = es.categories.confirmDeleteButton;
    deleteDialog.showModal();
  };

  document
    .getElementById('open-create-category')
    ?.addEventListener('click', () => {
      openFormModal('create');
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="edit-category"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['categoryId']);
        const name = btn.dataset['categoryName'] ?? '';
        if (!Number.isNaN(id) && id > 0) {
          openFormModal('edit', id, name);
        }
      });
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="delete-category"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['categoryId']);
        const name = btn.dataset['categoryName'] ?? '';
        if (!Number.isNaN(id) && id > 0) {
          openDeleteModal(id, name);
        }
      });
    });

  nameInput.addEventListener('input', () => {
    if (nameInput.getAttribute('aria-invalid') === 'true') {
      setFormError(null);
    }
  });

  formDialog.addEventListener('close', () => {
    formDialog.returnValue = '';
  });

  deleteDialog.addEventListener('close', () => {
    deleteDialog.returnValue = '';
  });

  categoryForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const trimmed = nameInput.value.trim();

    if (trimmed.length < 2 || trimmed.length > 100) {
      setFormError(es.categories.validationRequired);
      nameInput.focus();
      return;
    }

    submitButton.disabled = true;
    submitButton.textContent = es.categories.savingButton;
    setFormError(null);

    try {
      if (editingCategoryId !== null) {
        await updateCategory(editingCategoryId, { name: trimmed });
      } else {
        await createCategory({ name: trimmed });
      }
      formDialog.close();
      window.location.reload();
    } catch (error) {
      let message: string = es.categories.unexpectedError;
      if (error instanceof ApiError) {
        const nameErrors = error.errors?.['name'];
        message =
          Array.isArray(nameErrors) && nameErrors[0] !== undefined
            ? nameErrors[0]
            : error.message;
      }
      setFormError(message);
      submitButton.disabled = false;
      submitButton.textContent = es.categories.saveButton;
      nameInput.focus();
    }
  });

  confirmDeleteButton.addEventListener('click', async () => {
    if (deletingCategoryId === null) {
      return;
    }

    confirmDeleteButton.disabled = true;
    confirmDeleteButton.textContent = es.categories.deletingButton;
    deleteError.textContent = '';
    deleteError.hidden = true;

    try {
      await deleteCategory(deletingCategoryId);
      deleteDialog.close();
      window.location.reload();
    } catch (error) {
      deleteError.textContent =
        error instanceof ApiError
          ? error.message
          : es.categories.unexpectedError;
      deleteError.hidden = false;
      confirmDeleteButton.disabled = false;
      confirmDeleteButton.textContent = es.categories.confirmDeleteButton;
    }
  });
}
