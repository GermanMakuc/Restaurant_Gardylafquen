import { formatPriceFromCents } from '../../lib/format/currency';
import { es } from '../../lib/i18n/es';
import type { OrderItem } from '../../types/order';

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

export function initSalesManager(): void {
  const receiptDialog = document.getElementById('sale-receipt-dialog');
  if (!(receiptDialog instanceof HTMLDialogElement)) {
    return;
  }

  installLightDismissFallback([receiptDialog]);

  const titleEl = document.getElementById('sale-receipt-title');
  const metaEl = document.getElementById('sale-receipt-meta');
  const itemsEl = document.getElementById('sale-receipt-items');
  const subtotalEl = document.getElementById('sale-receipt-subtotal');
  const tipEl = document.getElementById('sale-receipt-tip');
  const totalEl = document.getElementById('sale-receipt-total');
  const printBtn = document.getElementById('sale-receipt-print-btn');

  if (
    !(titleEl instanceof HTMLElement) ||
    !(metaEl instanceof HTMLElement) ||
    !(itemsEl instanceof HTMLUListElement) ||
    !(subtotalEl instanceof HTMLElement) ||
    !(tipEl instanceof HTMLElement) ||
    !(totalEl instanceof HTMLElement) ||
    !(printBtn instanceof HTMLButtonElement)
  ) {
    return;
  }

  document
    .querySelectorAll<HTMLButtonElement>('[data-action="view-receipt"]')
    .forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = Number(btn.dataset['saleId']);
        const orderId = btn.dataset['saleOrderId'] ?? '';
        const tableLabel = btn.dataset['saleTable'] ?? '';
        const waiterName = btn.dataset['saleWaiter'] ?? '';
        const paidAt = btn.dataset['salePaidAt'] ?? '';
        const subtotal = Number(btn.dataset['saleSubtotal'] ?? '0');
        const tip = Number(btn.dataset['saleTip'] ?? '0');
        const total = Number(btn.dataset['saleTotal'] ?? '0');
        const tipPercent = btn.dataset['saleTipPercent'] ?? '10';
        const rawItems = btn.dataset['saleItems'] ?? '[]';

        let items: OrderItem[] = [];
        try {
          items = JSON.parse(rawItems) as OrderItem[];
        } catch {
          items = [];
        }

        titleEl.textContent = es.sales.receiptTitle(id);
        metaEl.textContent = `Orden #${orderId} · Mesa ${tableLabel} · ${waiterName} · ${paidAt}`;

        itemsEl.replaceChildren();
        for (const item of items) {
          const li = document.createElement('li');
          li.className = 'flex items-center justify-between gap-4 py-1 text-sm';
          const nameSpan = document.createElement('span');
          nameSpan.textContent = `${item.quantity} × ${item.product_name}`;
          const totalSpan = document.createElement('span');
          totalSpan.className = 'font-medium text-ink';
          totalSpan.textContent = formatPriceFromCents(item.line_total_cents);
          li.append(nameSpan, totalSpan);
          itemsEl.append(li);
        }

        subtotalEl.textContent = formatPriceFromCents(subtotal);
        tipEl.textContent = `${formatPriceFromCents(tip)} (${tipPercent}%)`;
        totalEl.textContent = formatPriceFromCents(total);

        receiptDialog.showModal();
      });
    });

  printBtn.addEventListener('click', () => {
    window.print();
  });

  receiptDialog.addEventListener('close', () => {
    receiptDialog.returnValue = '';
  });
}
