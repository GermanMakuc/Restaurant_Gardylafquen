import { createTable, deleteTable, updateTable } from '../../lib/api/tables';
import { ApiError } from '../../lib/api/client';
import { es } from '../../lib/i18n/es';
import type { TableStatus } from '../../types/table';

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

export function initTablesManager(): void {
  const formDialog = document.getElementById('table-form-dialog');
  const deleteDialog = document.getElementById('table-delete-dialog');

  if (
    !(formDialog instanceof HTMLDialogElement) ||
    !(deleteDialog instanceof HTMLDialogElement)
  ) {
    return;
  }

  installLightDismissFallback([formDialog, deleteDialog]);

  const formTitle = document.getElementById('table-form-title');
  const tableForm = document.getElementById('table-form');
  const numberInput = document.getElementById('table-number');
  const labelInput = document.getElementById('table-label');
  const capacityInput = document.getElementById('table-capacity');
  const statusSelect = document.getElementById('table-status');
  const formError = document.getElementById('table-form-error');
  const submitButton = document.getElementById('table-submit-btn');

  const deleteWarning = document.getElementById('table-delete-warning');
  const deleteError = document.getElementById('table-delete-error');
  const confirmDeleteButton = document.getElementById(
    'table-confirm-delete-btn',
  );

  if (
    !(formTitle instanceof HTMLElement) ||
    !(tableForm instanceof HTMLFormElement) ||
    !(numberInput instanceof HTMLInputElement) ||
    !(labelInput instanceof HTMLInputElement) ||
    !(capacityInput instanceof HTMLInputElement) ||
    !(statusSelect instanceof HTMLSelectElement) ||
    !(formError instanceof HTMLElement) ||
    !(submitButton instanceof HTMLButtonElement) ||
    !(deleteWarning instanceof HTMLElement) ||
    !(deleteError instanceof HTMLElement) ||
    !(confirmDeleteButton instanceof HTMLButtonElement)
  ) {
    return;
  }

  let editingTableId: number | null = null;
  let deletingTableId: number | null = null;

  const setFormError = (
    message: string | null,
    invalidControl?: HTMLElement,
  ): void => {
    for (const control of [numberInput, capacityInput, statusSelect]) {
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
    table?: {
      id: number;
      number: number;
      label: string;
      capacity: number;
      status: TableStatus;
    },
  ): void => {
    editingTableId = mode === 'edit' && table ? table.id : null;
    formTitle.textContent =
      mode === 'edit' ? es.tables.modalEditTitle : es.tables.modalCreateTitle;
    numberInput.value = table ? String(table.number) : '';
    labelInput.value = table?.label ?? '';
    capacityInput.value = table ? String(table.capacity) : '4';
    statusSelect.value = table?.status ?? 'available';

    setFormError(null);
    submitButton.disabled = false;
    submitButton.textContent = es.tables.saveButton;
    formDialog.showModal();
    numberInput.focus();
  };

  const openDeleteModal = (id: number, number: number): void => {
    deletingTableId = id;
    deleteWarning.textContent = es.tables.deleteWarning(number);
    deleteError.textContent = '';
    deleteError.hidden = true;
    confirmDeleteButton.disabled = false;
    confirmDeleteButton.textContent = es.tables.confirmDeleteButton;
    deleteDialog.showModal();
  };

  document
    .getElementById('open-create-table')
    ?.addEventListener('click', () => {
      openFormModal('create');
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="edit-table"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['tableId']);
        const number = Number(btn.dataset['tableNumber']);
        const capacity = Number(btn.dataset['tableCapacity']);
        const status = (btn.dataset['tableStatus'] ??
          'available') as TableStatus;
        if (!Number.isNaN(id) && id > 0) {
          openFormModal('edit', {
            id,
            number: Number.isNaN(number) ? 1 : number,
            label: btn.dataset['tableLabel'] ?? '',
            capacity: Number.isNaN(capacity) ? 4 : capacity,
            status,
          });
        }
      });
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="delete-table"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['tableId']);
        const number = Number(btn.dataset['tableNumber']);
        if (!Number.isNaN(id) && id > 0) {
          openDeleteModal(id, Number.isNaN(number) ? 0 : number);
        }
      });
    });

  for (const control of [numberInput, capacityInput]) {
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

  tableForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const tableNumber = Number(numberInput.value.trim());
    if (
      !Number.isInteger(tableNumber) ||
      tableNumber < 1 ||
      tableNumber > 9999
    ) {
      setFormError(es.tables.validationNumber, numberInput);
      numberInput.focus();
      return;
    }

    const capacity = Number(capacityInput.value.trim());
    if (!Number.isInteger(capacity) || capacity < 1 || capacity > 50) {
      setFormError(es.tables.validationCapacity, capacityInput);
      capacityInput.focus();
      return;
    }

    const labelTrimmed = labelInput.value.trim();
    const payload = {
      number: tableNumber,
      capacity,
      status: statusSelect.value as TableStatus,
      label: labelTrimmed !== '' ? labelTrimmed : null,
    };

    submitButton.disabled = true;
    submitButton.textContent = es.tables.savingButton;
    setFormError(null);

    try {
      if (editingTableId !== null) {
        await updateTable(editingTableId, payload);
      } else {
        await createTable(payload);
      }
      formDialog.close();
      window.location.reload();
    } catch (error) {
      let message: string = es.tables.unexpectedError;
      if (error instanceof ApiError) {
        const firstFieldError = error.errors
          ? Object.values(error.errors).flat()[0]
          : undefined;
        message = firstFieldError ?? error.message;
      }
      setFormError(message);
      submitButton.disabled = false;
      submitButton.textContent = es.tables.saveButton;
    }
  });

  confirmDeleteButton.addEventListener('click', async () => {
    if (deletingTableId === null) {
      return;
    }

    confirmDeleteButton.disabled = true;
    confirmDeleteButton.textContent = es.tables.deletingButton;
    deleteError.textContent = '';
    deleteError.hidden = true;

    try {
      await deleteTable(deletingTableId);
      deleteDialog.close();
      window.location.reload();
    } catch (error) {
      deleteError.textContent =
        error instanceof ApiError ? error.message : es.tables.unexpectedError;
      deleteError.hidden = false;
      confirmDeleteButton.disabled = false;
      confirmDeleteButton.textContent = es.tables.confirmDeleteButton;
    }
  });
}
