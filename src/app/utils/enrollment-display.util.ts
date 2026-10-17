/**
 * Commitment enrollment display helpers: show slab name with AR No.
 * Format: "Slab Name / AR No"
 */

export function formatSlabWithArNo(slabName: any, arNo: any): string {
  const slab = String(slabName == null ? '' : slabName).trim();
  const ar = String(arNo == null ? '' : arNo).trim();
  if (!slab && !ar) {
    return '';
  }
  if (!ar) {
    return slab;
  }
  if (slab && slab.indexOf(ar) >= 0) {
    return slab;
  }
  return slab ? slab + ' / ' + ar : ar;
}

/** Resolve slab + ar_no from enrollment / receipt / payment row shapes. */
export function slabLabelFromRow(row: any): string {
  if (!row) {
    return '';
  }
  let slab: any =
    (row.slab_info && typeof row.slab_info === 'object' && row.slab_info.slab_name) ||
    (typeof row.slab_info === 'string' ? row.slab_info : '') ||
    row.slab_name ||
    row.slabName ||
    '';
  if (typeof slab === 'object' && slab) {
    slab = slab.slab_name || '';
  }
  const ar =
    row.ar_no != null && row.ar_no !== ''
      ? row.ar_no
      : row.enrollment_det && row.enrollment_det.ar_no != null
        ? row.enrollment_det.ar_no
        : row.enrollment_info && row.enrollment_info.ar_no != null
          ? row.enrollment_info.ar_no
          : '';
  return formatSlabWithArNo(slab, ar);
}

export const SPN_PRIZED_TAG = 'SPN Prized';

/** prized_status may sit on the row itself or on its nested enrollment. */
export function isPrizedRow(row: any): boolean {
  if (!row) {
    return false;
  }
  const nested = row.enrollment_det || row.enrollment_info || row.enrol_info || {};
  return Number(row.prized_status) === 1 || row.prized_status === 'Prized' || Number(nested.prized_status) === 1;
}

export function withPrizedTag(label: string): string {
  const text = String(label || '');
  return text.indexOf(SPN_PRIZED_TAG) >= 0 ? text : (text ? text + ' · ' : '') + SPN_PRIZED_TAG;
}

/** Append AR No to enrol_name strings used in ledger / receipt dropdowns. */
export function enrolNameWithAr(row: any): string {
  if (!row) {
    return '';
  }
  const name = String(row.enrol_name || '').trim();
  const ar = String(row.ar_no == null ? '' : row.ar_no).trim();
  if (!name) {
    return slabLabelFromRow(row);
  }
  if (!ar || name.indexOf(ar) >= 0) {
    return name;
  }
  return name + ' | AR NO : ' + ar;
}

/** Mutate list rows so slab_name / enrol_name / slabName already include AR No. */
export function enrichRowsWithArNo(rows: any[]): any[] {
  if (!Array.isArray(rows)) {
    return [];
  }
  return rows.map((row) => {
    if (!row) {
      return row;
    }
    if (row.enrol_name) {
      row.enrol_name = enrolNameWithAr(row);
    }
    if (typeof row.slab_info === 'string') {
      row.slab_info = formatSlabWithArNo(row.slab_info, row.ar_no);
    } else if (row.slab_info && typeof row.slab_info === 'object' && row.slab_info.slab_name) {
      row.slab_info = Object.assign({}, row.slab_info, {
        slab_name: formatSlabWithArNo(row.slab_info.slab_name, row.ar_no),
      });
    }
    if (row.slab_name != null || row.ar_no != null || row.slab_info) {
      row.slab_name = slabLabelFromRow(row);
    }
    if (row.slabName != null || (row.ar_no != null && row.enrollment_det)) {
      row.slabName = formatSlabWithArNo(
        row.slabName || row.slab_name || '',
        row.ar_no || (row.enrollment_det && row.enrollment_det.ar_no)
      );
    }
    if (row.group_name && row.ar_no && String(row.group_name).indexOf('Commitment') >= 0) {
      row.group_name = formatSlabWithArNo(row.group_name, row.ar_no);
    }
    return row;
  });
}
