import { SPN_GROUP_STYLES } from './spn-groups/spn-new-group.component';

/** New-look report shell shared by the SPN reports (hero, filter card, KPI tiles, bars, tables). */
export const SPN_REPORT_STYLES = SPN_GROUP_STYLES + `
  .sr-kpis { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; margin-bottom: 14px; }
  .sr-kpi { border-radius: 16px; padding: 14px; background: #E9FFF3; }
  .sr-kpi span { display: block; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #5c6b80; }
  .sr-kpi b { display: block; margin-top: 4px; font-size: 21px; color: #071D3A; }
  .sr-kpi.blue { background: #EDF6FF; } .sr-kpi.gold { background: #FFF8E8; } .sr-kpi.rose { background: #FFF0F4; } .sr-kpi.violet { background: #F4EEFF; }
  .sr-split { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .sr-bar { display: grid; grid-template-columns: 160px 1fr 120px; gap: 10px; align-items: center; margin: 6px 0; font-size: 13px; }
  .sr-track { background: #eef3f0; border-radius: 8px; height: 12px; overflow: hidden; }
  .sr-fill { background: linear-gradient(90deg, #18A957, #006B3C); height: 100%; }
  .sr-fill.red { background: linear-gradient(90deg, #f87171, #DC2626); }
  .sr-title { font-weight: 800; color: #004D2A; margin-bottom: 8px; }
  .sr-right { text-align: right; }
  .sr-red { color: #DC2626; font-weight: 700; }
  .sr-green { color: #15803D; font-weight: 700; }
  .sr-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
  .sr-tabs button { border: 1px solid #006B3C; background: #fff; color: #006B3C; border-radius: 999px; padding: 6px 14px; font-weight: 800; }
  .sr-tabs button.on { background: #006B3C; color: #fff; }
  .sr-insight { background: #F4EEFF; border-left: 4px solid #7c3aed; border-radius: 10px; padding: 10px 12px; margin: 6px 0; font-size: 13px; }
  @media (max-width: 900px) { .sr-split { grid-template-columns: 1fr; } .sr-bar { grid-template-columns: 100px 1fr 90px; } }
`;

export function csvDownload(fileName: string, header: string[], rows: any[][]) {
  const esc = (v: any) => '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
  const lines = [header.map(esc).join(',')].concat(rows.map((r) => r.map(esc).join(',')));
  const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = fileName;
  link.click();
}

export function inr(v: any): string {
  return '₹ ' + (Number(v) || 0).toLocaleString('en-IN');
}

/** Branch-level logins see only their own branch. */
export function loginBranchId(): string {
  const b = localStorage.getItem('branch_id');
  return b && b !== '0' && b !== 'null' ? b : '';
}
