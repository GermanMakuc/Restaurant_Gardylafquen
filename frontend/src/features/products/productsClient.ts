import {
  createProduct,
  deleteProduct,
  updateProduct,
  uploadProductImage,
} from '../../lib/api/products';
import { ApiError } from '../../lib/api/client';
import {
  centsToInputValue,
  parsePriceInputToCents,
} from '../../lib/format/currency';
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

export function initProductsManager(): void {
  const formDialog = document.getElementById('product-form-dialog');
  const deleteDialog = document.getElementById('product-delete-dialog');

  if (
    !(formDialog instanceof HTMLDialogElement) ||
    !(deleteDialog instanceof HTMLDialogElement)
  ) {
    return;
  }

  installLightDismissFallback([formDialog, deleteDialog]);

  const formTitle = document.getElementById('product-form-title');
  const productForm = document.getElementById('product-form');
  const nameInput = document.getElementById('product-name');
  const categorySelect = document.getElementById('product-category');
  const priceInput = document.getElementById('product-price');
  const descriptionInput = document.getElementById('product-description');
  const imageInput = document.getElementById('product-image');
  const formError = document.getElementById('product-form-error');
  const submitButton = document.getElementById('product-submit-btn');

  const deleteWarning = document.getElementById('product-delete-warning');
  const deleteError = document.getElementById('product-delete-error');
  const confirmDeleteButton = document.getElementById(
    'product-confirm-delete-btn',
  );

  if (
    !(formTitle instanceof HTMLElement) ||
    !(productForm instanceof HTMLFormElement) ||
    !(nameInput instanceof HTMLInputElement) ||
    !(categorySelect instanceof HTMLSelectElement) ||
    !(priceInput instanceof HTMLInputElement) ||
    !(descriptionInput instanceof HTMLTextAreaElement) ||
    !(imageInput instanceof HTMLInputElement) ||
    !(formError instanceof HTMLElement) ||
    !(submitButton instanceof HTMLButtonElement) ||
    !(deleteWarning instanceof HTMLElement) ||
    !(deleteError instanceof HTMLElement) ||
    !(confirmDeleteButton instanceof HTMLButtonElement)
  ) {
    return;
  }

  let editingProductId: number | null = null;
  let deletingProductId: number | null = null;

  const setFormError = (
    message: string | null,
    invalidControl?: HTMLElement,
  ): void => {
    for (const control of [nameInput, categorySelect, priceInput]) {
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
    product?: {
      id: number;
      name: string;
      categoryId: number;
      priceCents: number;
      description: string;
    },
  ): void => {
    editingProductId = mode === 'edit' && product ? product.id : null;
    formTitle.textContent =
      mode === 'edit'
        ? es.products.modalEditTitle
        : es.products.modalCreateTitle;

    nameInput.value = product?.name ?? '';
    categorySelect.value = product ? String(product.categoryId) : '';
    priceInput.value = product ? centsToInputValue(product.priceCents) : '';
    descriptionInput.value = product?.description ?? '';
    imageInput.value = '';

    setFormError(null);
    submitButton.disabled = false;
    submitButton.textContent = es.products.saveButton;
    formDialog.showModal();
    nameInput.focus();
  };

  const openDeleteModal = (id: number, name: string): void => {
    deletingProductId = id;
    deleteWarning.textContent = es.products.deleteWarning(name);
    deleteError.textContent = '';
    deleteError.hidden = true;
    confirmDeleteButton.disabled = false;
    confirmDeleteButton.textContent = es.products.confirmDeleteButton;
    deleteDialog.showModal();
  };

  document
    .getElementById('open-create-product')
    ?.addEventListener('click', () => {
      openFormModal('create');
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="edit-product"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['productId']);
        const categoryId = Number(btn.dataset['productCategoryId']);
        const priceCents = Number(btn.dataset['productPriceCents']);
        if (!Number.isNaN(id) && id > 0) {
          openFormModal('edit', {
            id,
            name: btn.dataset['productName'] ?? '',
            categoryId: Number.isNaN(categoryId) ? 0 : categoryId,
            priceCents: Number.isNaN(priceCents) ? 0 : priceCents,
            description: btn.dataset['productDescription'] ?? '',
          });
        }
      });
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="delete-product"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['productId']);
        const name = btn.dataset['productName'] ?? '';
        if (!Number.isNaN(id) && id > 0) {
          openDeleteModal(id, name);
        }
      });
    });

  for (const control of [nameInput, categorySelect, priceInput]) {
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

  productForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const name = nameInput.value.trim();
    if (name.length < 2 || name.length > 150) {
      setFormError(es.products.validationName, nameInput);
      nameInput.focus();
      return;
    }

    const categoryId = Number(categorySelect.value);
    if (!Number.isInteger(categoryId) || categoryId <= 0) {
      setFormError(es.products.validationCategory, categorySelect);
      categorySelect.focus();
      return;
    }

    const priceCents = parsePriceInputToCents(priceInput.value);
    if (priceCents === null) {
      setFormError(es.products.validationPrice, priceInput);
      priceInput.focus();
      return;
    }

    const descriptionTrimmed = descriptionInput.value.trim();
    const payload = {
      name,
      category_id: categoryId,
      price_cents: priceCents,
      description: descriptionTrimmed !== '' ? descriptionTrimmed : null,
    };

    submitButton.disabled = true;
    submitButton.textContent = es.products.savingButton;
    setFormError(null);

    try {
      const saved =
        editingProductId !== null
          ? await updateProduct(editingProductId, payload)
          : await createProduct(payload);

      const selectedFile = imageInput.files?.[0];
      if (selectedFile !== undefined) {
        await uploadProductImage(saved.id, selectedFile);
      }

      formDialog.close();
      window.location.reload();
    } catch (error) {
      let message: string = es.products.unexpectedError;
      if (error instanceof ApiError) {
        const firstFieldError = error.errors
          ? Object.values(error.errors).flat()[0]
          : undefined;
        message = firstFieldError ?? error.message;
      }
      setFormError(message);
      submitButton.disabled = false;
      submitButton.textContent = es.products.saveButton;
    }
  });

  confirmDeleteButton.addEventListener('click', async () => {
    if (deletingProductId === null) {
      return;
    }

    confirmDeleteButton.disabled = true;
    confirmDeleteButton.textContent = es.products.deletingButton;
    deleteError.textContent = '';
    deleteError.hidden = true;

    try {
      await deleteProduct(deletingProductId);
      deleteDialog.close();
      window.location.reload();
    } catch (error) {
      deleteError.textContent =
        error instanceof ApiError ? error.message : es.products.unexpectedError;
      deleteError.hidden = false;
      confirmDeleteButton.disabled = false;
      confirmDeleteButton.textContent = es.products.confirmDeleteButton;
    }
  });
}
