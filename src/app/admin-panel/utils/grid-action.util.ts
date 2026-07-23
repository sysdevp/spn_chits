/** Base URL for hash-routing links inside ag-grid cell renderers. */
export function getGridReportBaseUrl(): string {
  const parts = location.href.split('#');
  return parts.length > 0 ? parts[0] : location.origin + '/';
}

export function gridDeleteAction(href: string, newTab = true): string {
  const target = newTab ? ' target="_blank"' : '';
  return (
    '<a class="rpt-grid-btn rpt-grid-btn--delete" href="' +
    href +
    '"' +
    target +
    ' title="Delete"><i class="fas fa-trash-alt"></i> Delete</a>'
  );
}

export function gridPrintAction(href: string, newTab = true): string {
  const target = newTab ? ' target="_blank"' : '';
  return (
    '<a class="rpt-grid-btn rpt-grid-btn--print" href="' +
    href +
    '"' +
    target +
    ' title="Print"><i class="fas fa-print"></i> Print</a>'
  );
}

export function gridEditAction(href: string, newTab = false): string {
  const target = newTab ? ' target="_blank"' : '';
  return (
    '<a class="rpt-grid-btn rpt-grid-btn--edit" href="' +
    href +
    '"' +
    target +
    ' title="Edit"><i class="fas fa-edit"></i> Edit</a>'
  );
}
