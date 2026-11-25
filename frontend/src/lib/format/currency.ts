export function formatPriceFromCents(priceCents: number): string {
  const amount = priceCents / 100;
  const hasDecimals = priceCents % 100 !== 0;

  return new Intl.NumberFormat('es-CL', {
    style: 'currency',
    currency: 'CLP',
    minimumFractionDigits: hasDecimals ? 2 : 0,
    maximumFractionDigits: 2,
  }).format(amount);
}

export function parsePriceInputToCents(raw: string): number | null {
  const normalized = raw.trim().replace(',', '.');
  if (normalized === '' || !/^\d+(\.\d{1,2})?$/.test(normalized)) {
    return null;
  }

  const parsed = Number(normalized);
  if (!Number.isFinite(parsed) || parsed < 0) {
    return null;
  }

  const cents = Math.round(parsed * 100);
  return cents <= 100_000_000 ? cents : null;
}

export function centsToInputValue(priceCents: number): string {
  if (priceCents % 100 === 0) {
    return String(priceCents / 100);
  }
  return (priceCents / 100).toFixed(2);
}
