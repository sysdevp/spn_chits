/**
 * Rebuilds ag-Grid after each report filter so columns and row data stay in sync.
 * Without this, re-assigning columnDefs + rowData on every Submit causes partial /
 * mismatched columns (different fields visible on each click).
 *
 * IMPORTANT: Do NOT wrap plain function cellRenderers that return HTML strings.
 * ag-grid 21 expects those as functions; wrapping them as component classes breaks
 * Actions/Print buttons on all rows except the first (see my-chits-new-updated).
 */

export function applyReportGridData(component: any, rowData: any[]): void {
  if (component.gridOptions) {
    component.gridOptions.suppressColumnVirtualisation = true;
  }

  const rows = Array.isArray(rowData) ? rowData.slice() : [];

  // Prefer API update when grid is already alive (no destroy/recreate).
  if (component.gridApi && typeof component.gridApi.setRowData === 'function') {
    try {
      if (component.columnDefs && component.columnDefs.length) {
        if (typeof component.gridApi.setColumnDefs === 'function') {
          component.gridApi.setColumnDefs(component.columnDefs);
        }
      }
      component.rowData = rows;
      component.gridApi.setRowData(rows);
      if (typeof component.gridApi.refreshCells === 'function') {
        component.gridApi.refreshCells({ force: true });
      }
      if (typeof component.gridApi.redrawRows === 'function') {
        component.gridApi.redrawRows();
      }
      component.showReportGrid = true;
      return;
    } catch (e) {
      /* fall through to binding update */
    }
  }

  component.rowData = rows;
  component.showReportGrid = true;
}
