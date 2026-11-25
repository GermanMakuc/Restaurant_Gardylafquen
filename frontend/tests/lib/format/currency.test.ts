import { describe, expect, it } from 'vitest';
import {
  centsToInputValue,
  formatPriceFromCents,
  parsePriceInputToCents,
} from '../../../src/lib/format/currency';

describe('currency helpers', () => {
  it('parses decimal and integer price inputs into integer cents', () => {
    expect(parsePriceInputToCents('4500')).toBe(450_000);
    expect(parsePriceInputToCents('45.50')).toBe(4550);
    expect(parsePriceInputToCents('45,5')).toBe(4550);
    expect(parsePriceInputToCents('0')).toBe(0);
    expect(parsePriceInputToCents('-10')).toBeNull();
    expect(parsePriceInputToCents('abc')).toBeNull();
  });

  it('formats cents for display and editable inputs', () => {
    expect(centsToInputValue(450_000)).toBe('4500');
    expect(centsToInputValue(4550)).toBe('45.50');
    expect(formatPriceFromCents(450_000)).toContain('4.500');
  });
});
