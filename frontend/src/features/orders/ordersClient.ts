import {
  createOrder,
  deleteOrder,
  updateOrder,
  updateOrderStatus,
} from '../../lib/api/orders';
import { ApiError } from '../../lib/api/client';
import { formatPriceFromCents } from '../../lib/format/currency';
import { es } from '../../lib/i18n/es';
import type { OrderItemInput, OrderStatus } from '../../types/order';

interface CartEntry {
  productId: number;
  productName: string;
  unitPriceCents: number;
  quantity: number;
}

interface CatalogProduct {
  id: number;
  name: string;
  priceCents: number;
}

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

export function initOrdersManager(): void {
  const formDialog = document.getElementById('order-form-dialog');
  const deleteDialog = document.getElementById('order-delete-dialog');

  if (
    !(formDialog instanceof HTMLDialogElement) ||
    !(deleteDialog instanceof HTMLDialogElement)
  ) {
    return;
  }

  installLightDismissFallback([formDialog, deleteDialog]);

  const formTitle = document.getElementById('order-form-title');
  const orderForm = document.getElementById('order-form');
  const tableSelect = document.getElementById('order-table');
  const waiterSelect = document.getElementById('order-waiter');
  const notesInput = document.getElementById('order-notes');
  const productSelect = document.getElementById('order-product-picker');
  const quantityInput = document.getElementById('order-item-quantity');
  const addItemButton = document.getElementById('order-add-item-btn');
  const cartList = document.getElementById('order-cart-list');
  const cartEmpty = document.getElementById('order-cart-empty');
  const subtotalDisplay = document.getElementById('order-subtotal-display');
  const formError = document.getElementById('order-form-error');
  const submitButton = document.getElementById('order-submit-btn');

  const deleteWarning = document.getElementById('order-delete-warning');
  const deleteError = document.getElementById('order-delete-error');
  const confirmDeleteButton = document.getElementById(
    'order-confirm-delete-btn',
  );

  if (
    !(formTitle instanceof HTMLElement) ||
    !(orderForm instanceof HTMLFormElement) ||
    !(tableSelect instanceof HTMLSelectElement) ||
    !(waiterSelect instanceof HTMLSelectElement) ||
    !(notesInput instanceof HTMLTextAreaElement) ||
    !(productSelect instanceof HTMLSelectElement) ||
    !(quantityInput instanceof HTMLInputElement) ||
    !(addItemButton instanceof HTMLButtonElement) ||
    !(cartList instanceof HTMLUListElement) ||
    !(cartEmpty instanceof HTMLElement) ||
    !(subtotalDisplay instanceof HTMLElement) ||
    !(formError instanceof HTMLElement) ||
    !(submitButton instanceof HTMLButtonElement) ||
    !(deleteWarning instanceof HTMLElement) ||
    !(deleteError instanceof HTMLElement) ||
    !(confirmDeleteButton instanceof HTMLButtonElement)
  ) {
    return;
  }

  const catalogById = new Map<number, CatalogProduct>();
  for (const option of Array.from(productSelect.options)) {
    const id = Number(option.value);
    const priceCents = Number(option.dataset['priceCents']);
    const name = option.dataset['productName'] ?? '';
    if (!Number.isNaN(id) && id > 0 && !Number.isNaN(priceCents)) {
      catalogById.set(id, { id, name, priceCents });
    }
  }

  let editingOrderId: number | null = null;
  let deletingOrderId: number | null = null;
  let cart: CartEntry[] = [];

  const setFormError = (
    message: string | null,
    invalidControl?: HTMLElement,
  ): void => {
    for (const control of [tableSelect, waiterSelect, productSelect]) {
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

  const renderCart = (): void => {
    cartList.replaceChildren();
    let subtotalCents = 0;

    if (cart.length === 0) {
      cartEmpty.hidden = false;
      subtotalDisplay.textContent = formatPriceFromCents(0);
      return;
    }

    cartEmpty.hidden = true;

    for (const entry of cart) {
      const lineTotal = entry.unitPriceCents * entry.quantity;
      subtotalCents += lineTotal;

      const li = document.createElement('li');
      li.className =
        'flex flex-wrap items-center justify-between gap-2 rounded-lg border border-border bg-surface px-3 py-2 text-sm';

      const info = document.createElement('div');
      const title = document.createElement('p');
      title.className = 'font-medium text-ink';
      title.textContent = entry.productName;
      const meta = document.createElement('p');
      meta.className = 'text-xs text-ink-muted';
      meta.textContent = `${entry.quantity} × ${formatPriceFromCents(entry.unitPriceCents)} = ${formatPriceFromCents(lineTotal)}`;
      info.append(title, meta);

      const controls = document.createElement('div');
      controls.className = 'flex items-center gap-1.5';

      const decBtn = document.createElement('button');
      decBtn.type = 'button';
      decBtn.className =
        'inline-flex size-8 items-center justify-center rounded-md border border-border bg-white text-xs font-semibold text-ink hover:bg-surface';
      decBtn.textContent = '−';
      decBtn.setAttribute(
        'aria-label',
        `Disminuir cantidad de ${entry.productName}`,
      );
      decBtn.addEventListener('click', () => {
        if (entry.quantity > 1) {
          entry.quantity -= 1;
        } else {
          cart = cart.filter((item) => item.productId !== entry.productId);
        }
        renderCart();
      });

      const incBtn = document.createElement('button');
      incBtn.type = 'button';
      incBtn.className =
        'inline-flex size-8 items-center justify-center rounded-md border border-border bg-white text-xs font-semibold text-ink hover:bg-surface';
      incBtn.textContent = '+';
      incBtn.setAttribute(
        'aria-label',
        `Aumentar cantidad de ${entry.productName}`,
      );
      incBtn.addEventListener('click', () => {
        entry.quantity += 1;
        renderCart();
      });

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className =
        'inline-flex h-8 items-center rounded-md border border-danger/30 bg-danger-soft px-2.5 text-xs font-semibold text-danger hover:bg-danger hover:text-white';
      removeBtn.textContent = es.orders.removeItemButton;
      removeBtn.setAttribute('aria-label', `Quitar ${entry.productName}`);
      removeBtn.addEventListener('click', () => {
        cart = cart.filter((item) => item.productId !== entry.productId);
        renderCart();
      });

      controls.append(decBtn, incBtn, removeBtn);
      li.append(info, controls);
      cartList.append(li);
    }

    subtotalDisplay.textContent = formatPriceFromCents(subtotalCents);
  };

  const syncTableOptions = (currentTableId?: number): void => {
    for (const option of Array.from(tableSelect.options)) {
      if (option.value === '') {
        continue;
      }
      const id = Number(option.value);
      const status = option.dataset['tableStatus'] ?? '';
      const isAllowed = status === 'available' || id === currentTableId;
      option.disabled = !isAllowed;
    }
  };

  const openFormModal = (
    mode: 'create' | 'edit',
    order?: {
      id: number;
      tableId: number;
      waiterId: number;
      notes: string;
      items: CartEntry[];
    },
  ): void => {
    editingOrderId = mode === 'edit' && order ? order.id : null;
    formTitle.textContent =
      mode === 'edit' ? es.orders.modalEditTitle : es.orders.modalCreateTitle;

    syncTableOptions(order?.tableId);
    tableSelect.value = order ? String(order.tableId) : '';
    waiterSelect.value = order ? String(order.waiterId) : '';
    notesInput.value = order?.notes ?? '';
    productSelect.value = '';
    quantityInput.value = '1';
    cart = order ? order.items.map((item) => ({ ...item })) : [];

    renderCart();
    setFormError(null);
    submitButton.disabled = false;
    submitButton.textContent = es.orders.saveButton;
    formDialog.showModal();
    tableSelect.focus();
  };

  const openDeleteModal = (id: number): void => {
    deletingOrderId = id;
    deleteWarning.textContent = es.orders.deleteWarning(id);
    deleteError.textContent = '';
    deleteError.hidden = true;
    confirmDeleteButton.disabled = false;
    confirmDeleteButton.textContent = es.orders.confirmDeleteButton;
    deleteDialog.showModal();
  };

  document
    .getElementById('open-create-order')
    ?.addEventListener('click', () => {
      openFormModal('create');
    });

  addItemButton.addEventListener('click', () => {
    const productId = Number(productSelect.value);
    const qty = Number(quantityInput.value.trim());
    const product = catalogById.get(productId);

    if (
      product === undefined ||
      !Number.isInteger(qty) ||
      qty < 1 ||
      qty > 999
    ) {
      return;
    }

    const existing = cart.find((entry) => entry.productId === productId);
    if (existing !== undefined) {
      existing.quantity += qty;
    } else {
      cart.push({
        productId: product.id,
        productName: product.name,
        unitPriceCents: product.priceCents,
        quantity: qty,
      });
    }

    setFormError(null);
    quantityInput.value = '1';
    renderCart();
  });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="edit-order"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['orderId']);
        const tableId = Number(btn.dataset['orderTableId']);
        const waiterId = Number(btn.dataset['orderWaiterId']);
        const rawItems = btn.dataset['orderItems'] ?? '[]';

        let parsedItems: CartEntry[] = [];
        try {
          const decoded = JSON.parse(rawItems) as Array<{
            product_id: number;
            product_name: string;
            unit_price_cents: number;
            quantity: number;
          }>;
          parsedItems = decoded.map((item) => ({
            productId: item.product_id,
            productName: item.product_name,
            unitPriceCents: item.unit_price_cents,
            quantity: item.quantity,
          }));
        } catch {
          parsedItems = [];
        }

        if (!Number.isNaN(id) && id > 0) {
          openFormModal('edit', {
            id,
            tableId: Number.isNaN(tableId) ? 0 : tableId,
            waiterId: Number.isNaN(waiterId) ? 0 : waiterId,
            notes: btn.dataset['orderNotes'] ?? '',
            items: parsedItems,
          });
        }
      });
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="status-order"]')
    .forEach((btn) => {
      btn.addEventListener('click', async () => {
        const id = Number(btn.dataset['orderId']);
        const nextStatus = btn.dataset['nextStatus'] as
          Extract<OrderStatus, 'waiting' | 'served' | 'cancelled'> | undefined;

        if (Number.isNaN(id) || id <= 0 || nextStatus === undefined) {
          return;
        }

        btn.disabled = true;
        try {
          await updateOrderStatus(id, nextStatus);
          window.location.reload();
        } catch {
          btn.disabled = false;
        }
      });
    });

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="delete-order"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['orderId']);
        if (!Number.isNaN(id) && id > 0) {
          openDeleteModal(id);
        }
      });
    });

  formDialog.addEventListener('close', () => {
    formDialog.returnValue = '';
  });

  deleteDialog.addEventListener('close', () => {
    deleteDialog.returnValue = '';
  });

  orderForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const tableId = Number(tableSelect.value);
    if (!Number.isInteger(tableId) || tableId <= 0) {
      setFormError(es.orders.validationTable, tableSelect);
      tableSelect.focus();
      return;
    }

    const waiterId = Number(waiterSelect.value);
    if (!Number.isInteger(waiterId) || waiterId <= 0) {
      setFormError(es.orders.validationWaiter, waiterSelect);
      waiterSelect.focus();
      return;
    }

    if (cart.length === 0) {
      setFormError(es.orders.validationItems, productSelect);
      productSelect.focus();
      return;
    }

    const notesTrimmed = notesInput.value.trim();
    const items: OrderItemInput[] = cart.map((entry) => ({
      product_id: entry.productId,
      quantity: entry.quantity,
    }));

    const payload = {
      table_id: tableId,
      waiter_id: waiterId,
      notes: notesTrimmed !== '' ? notesTrimmed : null,
      items,
    };

    submitButton.disabled = true;
    submitButton.textContent = es.orders.savingButton;
    setFormError(null);

    try {
      if (editingOrderId !== null) {
        await updateOrder(editingOrderId, payload);
      } else {
        await createOrder(payload);
      }
      formDialog.close();
      window.location.reload();
    } catch (error) {
      let message: string = es.orders.unexpectedError;
      if (error instanceof ApiError) {
        const firstFieldError = error.errors
          ? Object.values(error.errors).flat()[0]
          : undefined;
        message = firstFieldError ?? error.message;
      }
      setFormError(message);
      submitButton.disabled = false;
      submitButton.textContent = es.orders.saveButton;
    }
  });

  confirmDeleteButton.addEventListener('click', async () => {
    if (deletingOrderId === null) {
      return;
    }

    confirmDeleteButton.disabled = true;
    confirmDeleteButton.textContent = es.orders.deletingButton;
    deleteError.textContent = '';
    deleteError.hidden = true;

    try {
      await deleteOrder(deletingOrderId);
      deleteDialog.close();
      window.location.reload();
    } catch (error) {
      deleteError.textContent =
        error instanceof ApiError ? error.message : es.orders.unexpectedError;
      deleteError.hidden = false;
      confirmDeleteButton.disabled = false;
      confirmDeleteButton.textContent = es.orders.confirmDeleteButton;
    }
  });
}
