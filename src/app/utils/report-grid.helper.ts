/**
 * Updates report ag-Grid row data after each filter submit.
 *
 * Permanent rules (Action / Print buttons on EVERY row):
 * 1. Do NOT wrap HTML-string cellRenderer functions as Angular components.
 * 2. Do NOT call redrawRows/refreshCells after setRowData — that remounts
 *    HTML renderers incorrectly in ag-grid 21 and leaves buttons only on
 *    the last visible row.
 * 3. Never rely on CSS `top` / `display:flex` on `.ag-cell` (see styles.scss).
 */

import { enrichRowsWithArNo } from './enrollment-display.util';

export function applyReportGridData(component: any, rowData: any[]): void {
  if (component.gridOptions) {
    component.gridOptions.suppressColumnVirtualisation = true;
  }

  const rows = enrichRowsWithArNo(Array.isArray(rowData) ? rowData.slice() : []);

  // Prefer a plain binding update. setColumnDefs + redrawRows breaks HTML
  // Action/Print cellRenderers across rows in ag-grid community 21.
  component.rowData = rows;

  if (component.gridApi && typeof component.gridApi.setRowData === 'function') {
    try {
      component.gridApi.setRowData(rows);
    } catch (e) {
      /* binding already updated */
    }
  }

  if (typeof component.showReportGrid !== 'undefined') {
    component.showReportGrid = true;
  }
}
