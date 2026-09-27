const peso = new Intl.NumberFormat('en-PH', {
  style: 'currency',
  currency: 'PHP',
  currencyDisplay: 'narrowSymbol'
});

export function formatPeso(centavos: number): string {
  if (!Number.isInteger(centavos)) {
    throw new Error(`formatPeso expects integer centavos, got ${centavos}`);
  }

  return peso.format(centavos / 100);
}
