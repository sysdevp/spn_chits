/** Shared helpers for role dashboard views (bundle from get-chit-dashboard-bundle). */

export function dashNum(v: any): number {
  const n = Number(v);
  return isNaN(n) ? 0 : n;
}

export function dashSummary(bundle: any): any {
  return (bundle && bundle.summary) || {};
}

export function dashList(bundle: any, key: string): any[] {
  if (!bundle || !bundle[key]) {
    return [];
  }
  return Array.isArray(bundle[key]) ? bundle[key] : [];
}

/** Percent width string for bar fills (avoids style.width.% quirks). */
export function dashBarWidth(barData: number[], i: number): string {
  const data = barData || [];
  const max = Math.max.apply(null, data.concat([1]));
  const v = dashNum(data[i]);
  let pct = Math.round((v / max) * 100);
  if (v > 0 && pct < 8) {
    pct = 8;
  }
  return Math.min(100, pct) + '%';
}

export function dashHasBarData(barData: number[]): boolean {
  return (barData || []).some((x) => dashNum(x) > 0);
}

/** CSS conic-gradient for a 2-slice pie (pending vs released). */
export function dashPieGradient(pieData: number[]): string {
  const a = dashNum(pieData && pieData[0]);
  const b = dashNum(pieData && pieData[1]);
  const t = a + b;
  if (t <= 0) {
    return 'conic-gradient(#cfd8dc 0deg 360deg)';
  }
  const deg = Math.max(0.5, Math.min(359.5, (a / t) * 360));
  return `conic-gradient(#025E2D 0deg ${deg}deg, #D4AF37 ${deg}deg 360deg)`;
}

/** Inline styles string — used with styleUrls/global .role-dash class. */
export const ROLE_DASH_STYLES = `
  .rd-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.85rem;
    margin-bottom: 1.25rem;
  }
  .rd-kpi {
    background: #fff;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    box-shadow: 0 8px 20px rgba(1, 54, 24, 0.08);
    border-top: 3px solid #025E2D;
    min-height: 110px;
  }
  .rd-kpi.accent-gold { border-top-color: #D4AF37; }
  .rd-kpi.accent-teal { border-top-color: #0d9488; }
  .rd-kpi.accent-orange { border-top-color: #ea580c; }
  .rd-kpi.accent-red { border-top-color: #dc2626; }
  .rd-kpi.accent-purple { border-top-color: #7c3aed; }
  .rd-kpi-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 0.4rem;
  }
  .rd-kpi-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: #013618;
    line-height: 1.2;
  }
  .rd-kpi-note {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.35rem;
  }
  .rd-panel {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(1, 54, 24, 0.06);
    margin-bottom: 1rem;
    overflow: hidden;
  }
  .rd-panel-h {
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 700;
    color: #013618;
    font-size: 0.95rem;
  }
  .rd-panel-b { padding: 1rem 1.1rem; }
  .rd-placeholder {
    color: #94a3b8;
    font-size: 0.85rem;
    text-align: center;
    padding: 1.5rem 0.5rem;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
  }
  .rd-table { width: 100%; font-size: 0.85rem; }
  .rd-table th {
    background: #f0fdf4;
    color: #14532d;
    font-weight: 600;
    padding: 0.5rem 0.6rem;
    border-bottom: 1px solid #dcfce7;
  }
  .rd-table td {
    padding: 0.5rem 0.6rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
  }
  .rd-bar-track {
    height: 10px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
  }
  .rd-bar-fill {
    height: 100%;
    min-width: 0;
    background: linear-gradient(90deg, #025E2D, #20C25B);
    border-radius: 999px;
  }
  .rd-donut-wrap {
    position: relative;
    width: 160px;
    height: 160px;
    margin: 0 auto 0.75rem;
  }
  .rd-donut {
    width: 100%;
    height: 100%;
    border-radius: 50%;
  }
  .rd-donut-hole {
    position: absolute;
    inset: 28%;
    background: #fff;
    border-radius: 50%;
  }
  .rd-pie-legend {
    font-size: 0.82rem;
    color: #475569;
  }
  .rd-pie-swatch {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    margin-right: 0.35rem;
  }
  .rd-pie-swatch.s0 { background: #025E2D; }
  .rd-pie-swatch.s1 { background: #D4AF37; }
  .rd-chip {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    background: #dcfce7;
    color: #14532d;
  }
  .rd-actions a {
    color: #025E2D;
    cursor: pointer;
    margin-right: 0.35rem;
  }
  .rd-quick a {
    display: block;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    color: #013618;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    margin-bottom: 0.35rem;
    background: #f0fdf4;
  }
  .rd-quick a:hover { background: #dcfce7; }
`;
