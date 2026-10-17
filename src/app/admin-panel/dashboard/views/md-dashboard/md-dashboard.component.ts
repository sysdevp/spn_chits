import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges } from '@angular/core';
import { DomSanitizer, SafeResourceUrl, SafeStyle } from '@angular/platform-browser';
import { MenuPermissionService } from '../../../../Services/menu-permission.service';
import { ACCESS_PAGES_BY_ROUTE } from '../../../../config/spn-access.config';
import {
  dashBarWidth,
  dashHasBarData,
  dashList,
  dashNum,
  dashSummary,
  ROLE_DASH_STYLES,
} from '../dashboard-view.util';

const ADMIN_EXTRA_STYLES = `
  .hx { color: #10233f; font-family: Inter, "Segoe UI", sans-serif; }
  .hx-kpis { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
  .hx-leaders { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1.15fr) minmax(280px, 1fr); gap: 12px; margin-bottom: 12px; align-items: stretch; }
  .hx-ops { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 12px; }
  .hx-row3.mid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.35fr) minmax(0, 1fr); gap: 12px; margin-bottom: 12px; align-items: stretch; }
  .hx-bottom { display: grid; grid-template-columns: .9fr 1.35fr .85fr; gap: 12px; }
  .hx-kpi, .hx-panel.light { background: #fff; border-radius: 16px; padding: 12px 12px 8px; box-shadow: 0 8px 22px rgba(7, 29, 58, .06); text-decoration: none; color: inherit; border: 1px solid rgba(7, 29, 58, .05); position: relative; overflow: hidden; }
  .hx-kpi { min-height: 148px; cursor: pointer; padding-bottom: 34px; }
  .hx-kpi.green { background: #E9FFF3; } .hx-kpi.blue { background: #EDF6FF; } .hx-kpi.violet { background: #F3EDFF; }
  .hx-kpi.gold { background: #FFF8E8; } .hx-kpi.rose { background: #FFF0F4; } .hx-kpi.red { background: #FFF0F0; } .hx-kpi.cyan { background: #EAF8FF; }
  .hx-ico { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; color: #fff; margin-bottom: 8px; font-size: 13px; }
  .hx-ico.g { background: #18A957; } .hx-ico.b { background: #1687E8; } .hx-ico.p { background: #7A3FF2; }
  .hx-ico.y { background: #F2A900; } .hx-ico.r { background: #EF476F; } .hx-ico.d { background: #E53935; } .hx-ico.c { background: #159FD3; }
  .hx-l { font-size: 12px; color: #5c6b80; font-weight: 600; }
  .hx-v { font-size: 20px; font-weight: 800; color: #10233f; letter-spacing: -.02em; line-height: 1.15; margin-top: 2px; }
  .hx-v.big { font-size: 28px; color: #071D3A; }
  .hx-s, .hx-empty { font-size: 11px; color: #6d7c90; margin: 3px 0 0; font-weight: 500; }
  .hx-s.up { color: #18A957; font-weight: 700; }
  .hx-spark { position: absolute; left: 8px; right: 8px; bottom: 0; width: calc(100% - 16px); height: 32px; }
  .hx-board { position: relative; overflow: hidden; border-radius: 18px; min-height: 248px; padding: 14px 128px 12px 14px; color: #fff; box-shadow: 0 12px 28px rgba(7, 29, 58, .16); }
  .hx-board.emp { background: radial-gradient(120px 80px at 88% 78%, rgba(245,183,0,.22), transparent 70%), linear-gradient(160deg, #007A49 0%, #005C36 58%, #004D2A 100%); }
  .hx-board.branch { background: radial-gradient(140px 90px at 86% 70%, rgba(90,120,255,.28), transparent 72%), linear-gradient(160deg, #24306e 0%, #101B57 62%, #0c1444 100%); }
  .hx-art { position: absolute; right: -6px; bottom: -8px; width: 148px; height: 148px; object-fit: cover; pointer-events: none; }
  .hx-art.trophy { -webkit-mask-image: radial-gradient(circle at 48% 46%, #000 46%, transparent 72%); mask-image: radial-gradient(circle at 48% 46%, #000 46%, transparent 72%); }
  .hx-art.city { width: 156px; height: 156px; -webkit-mask-image: radial-gradient(circle at 52% 48%, #000 42%, transparent 74%); mask-image: radial-gradient(circle at 52% 48%, #000 42%, transparent 74%); }
  .hx-board-head, .hx-ph { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px; position: relative; z-index: 1; }
  .hx-board-title, .hx-ph-title { font-size: 14px; font-weight: 700; letter-spacing: -.01em; }
  .hx-pill, .hx-ph a { border: 1px solid rgba(255,255,255,.75); color: #fff; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; text-decoration: none; background: rgba(255,255,255,.08); white-space: nowrap; }
  .hx-panel.light .hx-ph a { border-color: #d5e0ea; color: #1687E8; background: #fff; }
  .hx-ph small { color: #6d7c90; font-weight: 600; background: #f4f7fb; border-radius: 999px; padding: 3px 8px; }
  .hx-lead { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; position: relative; z-index: 1; }
  .hx-medal { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex: 0 0 34px; border: 2px solid rgba(255,255,255,.85); box-shadow: 0 2px 0 rgba(0,0,0,.18), inset 0 2px 0 rgba(255,255,255,.65), inset 0 -3px 0 rgba(0,0,0,.12); }
  .hx-medal.gold { background: linear-gradient(180deg, #FFE58A 0%, #F5B700 48%, #C48400 100%); color: #5c3b00; }
  .hx-medal.silver { background: linear-gradient(180deg, #ffffff 0%, #d5dee8 52%, #8e9bab 100%); color: #243140; }
  .hx-medal.bronze { background: linear-gradient(180deg, #ffd0a0 0%, #e08a3c 52%, #a85d22 100%); color: #fff; }
  .hx-photo { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex: 0 0 36px; border: 2px solid rgba(255,255,255,.85); background: #fff; }
  .hx-pin { width: 36px; height: 36px; border-radius: 50%; flex: 0 0 36px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,.35); }
  .hx-pin svg { width: 18px; height: 18px; fill: #fff; }
  .hx-pin.gold { background: radial-gradient(circle at 30% 30%, #ffe58a, #F5B700); }
  .hx-pin.silver { background: radial-gradient(circle at 30% 30%, #9fd4ff, #1687E8); }
  .hx-pin.bronze { background: radial-gradient(circle at 30% 30%, #ffd0a0, #e08a3c); }
  .hx-lead-main { flex: 1; min-width: 0; }
  .hx-lead-line { display: flex; justify-content: space-between; gap: 8px; align-items: flex-start; }
  .hx-lead-who b { display: block; font-size: 13px; font-weight: 700; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
  .hx-lead-who small { display: block; margin-top: 1px; font-size: 10px; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.72); font-weight: 600; }
  .hx-lead-amt { text-align: right; font-weight: 800; font-size: 13px; white-space: nowrap; }
  .hx-lead-amt small { display: block; font-size: 11px; font-weight: 700; color: rgba(255,255,255,.8); }
  .hx-track { height: 7px; margin-top: 6px; border-radius: 99px; background: rgba(255,255,255,.22); overflow: hidden; }
  .hx-track span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #7CFFC4, #2FE3A1); }
  .hx-track.blue span { background: linear-gradient(90deg, #8eb6ff, #4CC3FF); }
  .hx-track.light { background: rgba(255,255,255,.35); }
  .hx-track.light span { background: #fff; }
  .hx-empty.on-dark { color: rgba(255,255,255,.8); }
  .hx-panel.target { display: flex; flex-direction: column; height: 100%; min-height: 248px; }
  .hx-target-body { flex: 1; align-items: center; }
  .hx-panel.light .hx-ph-title, .hx-panel.light .hx-v { color: #10233f; }
  .hx-mini-ico { width: 22px; height: 22px; border-radius: 7px; background: #E9FFF3; color: #18A957; display: inline-flex; align-items: center; justify-content: center; margin-right: 6px; font-size: 11px; }
  .hx-target-body { display: flex; align-items: center; gap: 8px; }
  .hx-target-copy { min-width: 0; }
  .hx-target-copy .hx-v { font-size: 16px; }
  .hx-gauge-wrap { position: relative; width: 148px; height: 148px; flex: 0 0 148px; }
  .hx-gauge-wrap.sm { width: 132px; height: 132px; flex: 0 0 132px; margin: 0; }
  .hx-gauge { width: 100%; height: 100%; border-radius: 50%; }
  .hx-gauge.sm { width: 74px; height: 74px; }
  .hx-gauge-hole { position: absolute; top: 18%; right: 18%; bottom: 18%; left: 18%; background: #fff; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; font-weight: 800; color: #18A957; font-size: 18px; line-height: 1; }
  .hx-gauge-hole small { display: block; margin-top: 2px; font-size: 10px; color: #7b8ba0; font-weight: 600; }
  .hx-legend { font-size: 12px; margin-top: 6px; color: #445266; font-weight: 600; }
  .hx-legend i, .hx-map-legend i { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
  .hx-legend i.g, .hx-chart-key i { background: #18A957; }
  .hx-legend i.b { background: #1687E8; }
  .hx-legend i.wait { background: #DCE5EF; }
  .hx-op { position: relative; overflow: hidden; border-radius: 16px; min-height: 108px; padding: 12px 14px; color: #fff; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 22px rgba(7, 29, 58, .1); }
  .hx-op .hx-l, .hx-op .hx-s, .hx-op .hx-v { color: #fff; }
  .hx-op .hx-v { font-size: 26px; line-height: 1.1; }
  .hx-op .hx-s { color: rgba(255,255,255,.88); }
  .hx-op.blue { background: linear-gradient(135deg, #3AA0FF 0%, #1687E8 55%, #0E6FCB 100%); }
  .hx-op.orange { background: linear-gradient(135deg, #FFC14D 0%, #F5A623 58%, #E8890B 100%); }
  .hx-op.purple { background: linear-gradient(135deg, #9A6BFF 0%, #7040E8 58%, #5A2ED4 100%); }
  .hx-op.pink { background: linear-gradient(135deg, #FF7AA8 0%, #EF5B8F 55%, #E23D78 100%); }
  .hx-op-ico { width: 30px; height: 30px; border-radius: 9px; background: rgba(255,255,255,.22); display: flex; align-items: center; justify-content: center; margin-bottom: 4px; font-size: 14px; }
  .hx-ring { position: relative; width: 62px; height: 62px; flex: 0 0 62px; }
  .hx-ring-hole { position: absolute; top: 18%; right: 18%; bottom: 18%; left: 18%; background: #fff; color: #1687E8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; }
  .hx-deco { position: absolute; right: 4px; bottom: 2px; width: 64px; height: 64px; opacity: .95; }
  .route-card { display: flex; flex-direction: column; min-height: 280px; }
  .hx-route { display: flex; align-items: center; gap: 12px; flex: 1; }
  .hx-map-frame { position: relative; }
  .hx-map { width: 100%; height: 220px; border: 0; border-radius: 12px; }
  .hx-map-legend { position: absolute; top: 10px; right: 10px; background: #fff; border-radius: 10px; padding: 8px 10px; font-size: 11px; font-weight: 700; color: #243044; box-shadow: 0 6px 16px rgba(7,29,58,.12); }
  .hx-map-legend i.on { background: #18A957; } .hx-map-legend i.route { background: #1687E8; } .hx-map-legend i.idle { background: #C5CDD6; }
  .hx-dots { margin-top: 8px; font-size: 12px; font-weight: 600; }
  .hx-dots i, .hx-legend b { font-style: normal; }
  .hx-dots i.on { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #b6f3c8; margin-right: 4px; }
  .hx-dots i.off { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ffb3b3; margin-right: 4px; }
  a.hx-op, a.hx-alert { text-decoration: none; color: inherit; cursor: pointer; }
  a.hx-op:hover, a.hx-kpi:hover, .hx-click:hover { transform: translateY(-2px); transition: transform .15s; }
  .hx-click { cursor: pointer; }
  .hx-of { font-size: 13px; font-weight: 600; opacity: .85; }
  .hx-muted { display: block; font-size: 10px; color: #7b8ba0; font-weight: 600; }
  .hx-approve-btn { display: inline-block; margin-top: 10px; background: #18A957; color: #fff !important; border-radius: 999px; padding: 5px 14px; font-size: 12px; font-weight: 700; text-decoration: none; }
  .hx-s.link { font-weight: 700; }
  .hx-route-key .hx-legend b { margin-left: 6px; }
  .hx-route { align-items: center; }
  .hx-route-art { width: 56px; height: 108px; margin-left: auto; flex: 0 0 56px; }
  .exp-card { position: relative; padding-right: 78px; }
  .hx-bag { position: absolute; right: 10px; bottom: 12px; width: 70px; height: 78px; pointer-events: none; }
  .hx-op .hx-deco.bars { width: 92px; height: 70px; bottom: 8px; }
  .hx-map-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
  .hx-map-chips span { background: #EAF8FF; color: #0e6ea8; border-radius: 99px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
  .hx-exp { display: flex; align-items: center; gap: 8px; padding: 7px 0; border-bottom: 1px solid #eef3f8; font-size: 13px; }
  .hx-exp b { margin-left: auto; color: #10233f; }
  .hx-exp-name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .hx-exp-ico { width: 22px; height: 22px; border-radius: 7px; flex: 0 0 22px; }
  .hx-exp-ico.fuel { background: #18A957; } .hx-exp-ico.travel { background: #1687E8; }
  .hx-exp-ico.food { background: #F5A623; } .hx-exp-ico.other { background: #7040E8; }
  .hx-vchart { display: flex; align-items: flex-end; gap: 8px; height: 150px; padding-top: 8px; }
  .hx-vcol { flex: 1; min-width: 0; text-align: center; }
  .hx-vbar-track { height: 120px; display: flex; align-items: flex-end; justify-content: center; }
  .hx-vbar-track span { display: block; width: 16px; border-radius: 6px 6px 3px 3px; background: linear-gradient(180deg, #3DDC97, #18A957); min-height: 6px; }
  .hx-vcol em { display: block; margin-top: 6px; font-style: normal; font-size: 10px; color: #6d7c90; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .hx-chart-key { margin-top: 8px; font-size: 12px; color: #445266; font-weight: 600; display: flex; gap: 12px; align-items: center; }
  .hx-chart-key i.line { width: 14px; height: 3px; border-radius: 99px; background: #1687E8; display: inline-block; }
  .hx-combo { position: relative; }
  .hx-combo-line { position: absolute; left: 0; right: 0; top: 0; height: 120px; width: 100%; pointer-events: none; }
  .hx-target-copy .hx-v { white-space: nowrap; font-size: 18px; }
  .alert-card { position: relative; padding-right: 72px; }
  .hx-bell { position: absolute; right: 8px; bottom: 10px; width: 58px; height: 66px; pointer-events: none; }
  .hx-table-wrap { overflow-x: auto; }
  .hx table { width: 100%; font-size: 12px; border-collapse: collapse; }
  .hx th { text-align: left; padding: 6px 6px; color: #7b8ba0; font-weight: 700; border-bottom: 1px solid #e7eef5; }
  .hx td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #f2f6fa; color: #243044; font-weight: 600; }
  .hx-badge { background: #FFF4D2; color: #9a6b00; border-radius: 999px; padding: 3px 8px; font-weight: 700; font-size: 11px; }
  .hx-badge.ok { background: #E5F8EC; color: #137a32; }
  .hx-alert { display: flex; gap: 8px; align-items: flex-start; background: #f7fafc; border-radius: 12px; padding: 8px; margin-bottom: 8px; }
  .hx-alert small { display: block; color: #6d7c90; font-weight: 500; }
  .hx-alert-ico { width: 28px; height: 28px; border-radius: 9px; color: #fff; display: flex; align-items: center; justify-content: center; flex: 0 0 28px; font-size: 12px; }
  .hx-alert-ico.rose { background: #EF476F; } .hx-alert-ico.purple { background: #7040E8; }
  .hx-alert-ico.gold { background: #F5B700; } .hx-alert-ico.green { background: #18A957; }
  @media (max-width: 1200px) {
    .hx-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .hx-leaders, .hx-row3.mid, .hx-bottom { grid-template-columns: 1fr 1fr; }
    .hx-panel.target { grid-column: 1 / -1; }
  }
  @media (max-width: 700px) {
    .hx-kpis, .hx-ops, .hx-leaders, .hx-row3.mid, .hx-bottom { grid-template-columns: 1fr; }
    .hx-board { padding-right: 16px; }
    .hx-art { display: none; }
    .hx-table-wrap { overflow-x: auto; }
  }
  .ad-wrap { color: #334155; }
  .ad-kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
  }
  .ad-kpi {
    background: #fff;
    border: 1px solid #e8eef3;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    min-height: 108px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    position: relative;
  }
  .ad-kpi-ico {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-bottom: 0.55rem;
    color: #fff;
  }
  .ad-kpi-ico.g { background: #16a34a; }
  .ad-kpi-ico.b { background: #2563eb; }
  .ad-kpi-ico.p { background: #025E2D; }
  .ad-kpi-ico.t { background: #C4A35A; }
  .ad-kpi-ico.o { background: #ea580c; }
  .ad-kpi-ico.r { background: #dc2626; }
  .ad-kpi-ico.y { background: #ca8a04; }
  .ad-kpi-l {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 0.25rem;
  }
  .ad-kpi-v {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
  }
  .ad-kpi-trend {
    font-size: 0.72rem;
    margin-top: 0.35rem;
    color: #16a34a;
    font-weight: 600;
  }
  .ad-kpi-trend.down { color: #dc2626; }
  .ad-kpi-trend.muted { color: #94a3b8; font-weight: 500; }
  .ad-prog {
    height: 6px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.45rem;
  }
  .ad-prog > span {
    display: block;
    height: 100%;
    background: linear-gradient(90deg, #16a34a, #22c55e);
    border-radius: 999px;
  }
  .ad-mini-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.65rem;
    margin-bottom: 1rem;
  }
  .ad-mini {
    background: #fff;
    border: 1px solid #e8eef3;
    border-radius: 10px;
    padding: 0.75rem 0.85rem;
    text-align: center;
  }
  .ad-mini-l { font-size: 0.7rem; color: #64748b; font-weight: 600; margin-bottom: 0.3rem; }
  .ad-mini-v { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
  .ad-mini-t { font-size: 0.68rem; color: #16a34a; margin-top: 0.2rem; }
  .ad-spark-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
    margin-bottom: 0.5rem;
  }
  .ad-spark {
    background: #fff;
    border: 1px solid #e8eef3;
    border-radius: 12px;
    padding: 0.85rem 1rem;
  }
  .ad-spark-l { font-size: 0.72rem; color: #64748b; font-weight: 600; }
  .ad-spark-v { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0.2rem 0; }
  .ad-spark-t { font-size: 0.72rem; font-weight: 600; color: #16a34a; }
  .ad-spark-t.down { color: #dc2626; }
  .ad-spark svg { width: 100%; height: 36px; margin-top: 0.35rem; display: block; }
  .ad-trend-svg { width: 100%; height: 180px; display: block; }
  .ad-vbars {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 2rem;
    height: 160px;
    padding-bottom: 1.5rem;
  }
  .ad-vbar-col { text-align: center; width: 56px; }
  .ad-vbar {
    width: 48px;
    margin: 0 auto 0.4rem;
    border-radius: 8px 8px 4px 4px;
    background: #cbd5e1;
    min-height: 8px;
  }
  .ad-vbar.ach { background: linear-gradient(180deg, #22c55e, #16a34a); }
  .ad-vbar-lbl { font-size: 0.72rem; color: #64748b; }
  .ad-vbar-amt { font-size: 0.7rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem; }
  .ad-gauge-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0.5rem auto 0;
  }
  .ad-gauge {
    width: 100%;
    height: 100%;
    border-radius: 50%;
  }
  .ad-gauge-hole {
    position: absolute;
    inset: 22%;
    background: #fff;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .ad-gauge-pct { font-size: 1.35rem; font-weight: 800; color: #16a34a; line-height: 1; }
  .ad-gauge-sub { font-size: 0.65rem; color: #94a3b8; font-weight: 600; }
  .ad-branch-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.55rem;
    font-size: 0.82rem;
  }
  .ad-branch-name { width: 110px; font-weight: 600; color: #334155; flex-shrink: 0; }
  .ad-branch-amt { width: 90px; text-align: right; font-weight: 700; color: #0f172a; flex-shrink: 0; }
  .ad-map-box {
    min-height: 200px;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    background: linear-gradient(160deg, #f0fdf4 0%, #e0f2fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 0.85rem;
    text-align: center;
    padding: 1rem;
  }
  .ad-fin-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
  }
  .ad-fin-row:last-child { border-bottom: 0; }
  .ad-fin-l { display: flex; align-items: center; gap: 0.5rem; color: #475569; }
  .ad-fin-ico {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; color: #fff;
  }
  .ad-alert {
    display: flex;
    gap: 0.65rem;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.82rem;
  }
  .ad-alert:last-child { border-bottom: 0; }
  .ad-alert-dot {
    width: 8px; height: 8px; border-radius: 50%;
    margin-top: 0.35rem; flex-shrink: 0;
  }
  .ad-alert-dot.red { background: #dc2626; }
  .ad-alert-dot.orange { background: #ea580c; }
  .ad-alert-dot.green { background: #16a34a; }
  .ad-alert-dot.blue { background: #2563eb; }
  .ad-biz-legend span { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 0.3rem; }
  .ad-panel-h-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ad-link {
    font-size: 0.78rem;
    color: #16a34a;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
  }
  .ad-rank {
    display: inline-flex;
    width: 22px; height: 22px;
    align-items: center; justify-content: center;
    border-radius: 50%;
    background: #dcfce7;
    color: #14532d;
    font-size: 0.72rem;
    font-weight: 700;
  }
`;

@Component({
  selector: 'app-md-dashboard',
  templateUrl: './md-dashboard.component.html',
  styles: [ROLE_DASH_STYLES, ADMIN_EXTRA_STYLES],
})
export class MdDashboardComponent implements OnInit, OnChanges {
  @Input() bundle: any;
  @Input() barLabels: string[] = [];
  @Input() barData: number[] = [];
  @Input() barScope = 'month';
  @Input() pieLabels: string[] = [];
  @Input() pieData: number[] = [];
  @Input() pieSafeStyle: SafeStyle;
  @Output() openDetail = new EventEmitter<string>();

  sparkPoints = '0,28 20,22 40,24 60,18 80,20 100,12 120,16 140,8';
  trendPoints = '';
  trendArea = '';
  gaugeSafe: SafeStyle;
  workSafe: SafeStyle;
  attendSafe: SafeStyle;
  bizDonutSafe: SafeStyle;
  fieldMapSafe: SafeResourceUrl;

  constructor(private sanitizer: DomSanitizer, private menuPermission: MenuPermissionService) {
    this.gaugeSafe = this.sanitizer.bypassSecurityTrustStyle(
      'conic-gradient(#18A957 0deg 0deg, #DCE5EF 0deg 360deg)'
    );
    this.workSafe = this.gaugeSafe;
    this.attendSafe = this.gaugeSafe;
    this.fieldMapSafe = this.sanitizer.bypassSecurityTrustResourceUrl('about:blank');
    this.bizDonutSafe = this.sanitizer.bypassSecurityTrustStyle(
      'conic-gradient(#16a34a 0 245deg, #94a3b8 245deg 310deg, #38bdf8 310deg 346deg, #cbd5e1 346deg 360deg)'
    );
  }

  ngOnInit() {
    this.refreshDerived();
  }

  ngOnChanges(changes: SimpleChanges) {
    this.refreshDerived();
  }

  s() { return dashSummary(this.bundle); }
  n(v: any) { return dashNum(v); }
  collections() { return dashList(this.bundle, 'today_collections'); }
  outstanding() { return dashList(this.bundle, 'outstanding_customers'); }
  followups() { return dashList(this.bundle, 'today_followups'); }
  auctions() { return dashList(this.bundle, 'today_auctions'); }
  todayTasks() { return dashList(this.bundle, 'today_tasks'); }
  settlements() { return dashList(this.bundle, 'recent_settlements'); }
  tasksDone() {
    return this.todayTasks().filter((t) => /complete|done|closed/i.test(String(t.status || ''))).length;
  }
  tasksPending() {
    return Math.max(0, this.n(this.s().today_tasks_count) - this.tasksDone());
  }
  initial(name: string) {
    return (name || 'E').trim().charAt(0).toUpperCase();
  }
  portrait(i: number) {
    const pics = [
      'assets/img/spn-portrait-1.png',
      'assets/img/spn-portrait-2.png',
      'assets/img/spn-portrait-3.png',
    ];
    return pics[i % pics.length];
  }
  pendingPct() {
    return Math.max(0, 100 - this.targetAchievedPct());
  }
  todayTargetPct() {
    const target = this.n(this.s().collection_target);
    if (target <= 0) {
      return 0;
    }
    return Math.min(100, Math.round((this.n(this.s().today_collection_total) / target) * 100));
  }
  sparkLine(seed: number) {
    const ys = [22, 18, 20, 14, 16, 10, 12, 6].map((y, i) => {
      const shift = ((seed * 3 + i * 5) % 7) - 3;
      return Math.max(4, Math.min(26, y + shift));
    });
    return 'M' + ys.map((y, i) => (i * 20) + ' ' + y).join(' L');
  }
  sparkArea(seed: number) {
    return this.sparkLine(seed) + ' L140 32 L0 32 Z';
  }
  fieldMapHtml() {
    const names = this.branchRows().slice(0, 6).map((b) => {
      return String(b.branch_name || '').replace(/</g, '').replace(/>/g, '');
    }).filter((n) => n);
    const chips = names.length
      ? names.map((n) => '<span>' + n + '</span>').join('')
      : '<span>Coimbatore region</span>';
    return '<!DOCTYPE html><html><head><meta charset="utf-8">'
      + '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>'
      + '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>'
      + '<style>html,body,#map{height:100%;margin:0}#map{height:100%}.chips{position:absolute;z-index:500;left:8px;bottom:8px;display:flex;gap:6px;flex-wrap:wrap;max-width:70%}.chips span{background:#fff;color:#0e6ea8;border-radius:99px;padding:2px 8px;font:700 11px Inter,sans-serif;box-shadow:0 2px 6px rgba(0,0,0,.12)}</style>'
      + '</head><body><div id="map"></div><div class="chips">' + chips + '</div><script>'
      + 'var map=L.map("map",{zoomControl:true,attributionControl:false}).setView([11.0168,76.9558],10);'
      + 'L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}",{maxZoom:19}).addTo(map);'
      + 'L.circleMarker([11.0168,76.9558],{radius:8,color:"#18A957",fillColor:"#18A957",fillOpacity:1}).addTo(map);'
      + '</script></body></html>';
  }
  /** Card link to its overview; null (not clickable) when the role lacks that overview. */
  hub(key: string) {
    const ids = ACCESS_PAGES_BY_ROUTE['hub/' + key] || [];
    if (ids.length && !this.menuPermission.canAnyPage(ids)) {
      return null;
    }
    return '/admin-panel/hub/' + key;
  }
  attendancePct() {
    const total = this.n(this.s().active_employees);
    return total > 0 ? Math.round((this.n(this.s().present_today) / total) * 100) : 0;
  }
  birthdayNames() {
    return dashList(this.bundle, 'employee_birthdays_today').map((b) => b.name).filter((x) => x).slice(0, 3).join(', ');
  }
  pendingSettlements() {
    return this.settlements().filter((r) => Number(r.is_approved) !== 1);
  }
  expenseRows() {
    const kinds = ['fuel', 'travel', 'food', 'other'];
    return dashList(this.bundle, 'today_expenses').slice(0, 4).map((row, i) => {
      return {
        kind: kinds[i] || 'other',
        label: row.label || 'Expense',
        amount: row.amount
      };
    });
  }
  barHeight(i: number) {
    const data = (this.barData || []).map((x) => this.n(x));
    const max = Math.max.apply(null, data.concat([1]));
    const px = Math.round((this.n(this.barData[i]) / max) * 120);
    return Math.max(6, px) + 'px';
  }
  topEmployees() {
    return dashList(this.bundle, 'top_employees').slice(0, 3);
  }
  branchRows() { return dashList(this.bundle, 'branch_collections'); }
  hasBars() { return dashHasBarData(this.barData); }
  barWidth(i: number) { return dashBarWidth(this.barData, i); }
  hasPie() { return (this.pieData || []).some((x) => dashNum(x) > 0); }

  totalGroups(): number {
    return this.n(this.s().running_groups) + this.n(this.s().vacant_groups);
  }

  achievedPct(): number {
    const pending = this.n(this.s().total_bid_pending);
    const released = this.n(this.s().total_bid_released);
    const t = pending + released;
    if (t <= 0) {
      return 0;
    }
    return Math.round((released / t) * 100);
  }

  targetAchievedPct(): number {
    const salesPct = this.n(this.s().sales_target_percent);
    if (salesPct > 0) {
      return Math.min(100, salesPct);
    }
    const target = this.n(this.s().sales_target || this.s().monthly_target);
    if (target <= 0) {
      return 0;
    }
    const achieved = this.n(
      this.s().sales_target_achieved || this.s().monthly_target_achieved || this.s().current_month_collection
    );
    return Math.min(100, Math.round((achieved / target) * 100));
  }

  targetBarH(): string {
    return '100%';
  }

  achievedBarH(): string {
    const pct = this.targetAchievedPct();
    return Math.max(12, Math.min(100, pct || 12)) + '%';
  }

  overdueTotal(): number {
    return this.outstanding().reduce(
      (sum, o) => sum + this.n(o.pending_amount || o.amount || o.outstanding),
      0
    );
  }

  refreshDerived() {
    const data = (this.barData || []).map((x) => this.n(x));
    if (data.length) {
      const max = Math.max.apply(null, data.concat([1]));
      const w = 140;
      const h = 36;
      const step = data.length > 1 ? w / (data.length - 1) : w;
      const pts = data.map((v, i) => {
        const x = Math.round(i * step);
        const y = Math.round(h - (v / max) * (h - 4) - 2);
        return x + ',' + y;
      });
      this.sparkPoints = pts.join(' ');
      const th = 160;
      const tw = 320;
      const tstep = data.length > 1 ? tw / (data.length - 1) : tw;
      const tpts = data.map((v, i) => {
        const x = Math.round(i * tstep);
        const y = Math.round(th - (v / max) * (th - 20) - 10);
        return { x, y };
      });
      this.trendPoints = tpts.map((p) => p.x + ',' + p.y).join(' ');
      this.trendArea =
        '0,' + th + ' ' +
        tpts.map((p) => p.x + ',' + p.y).join(' ') +
        ' ' + tw + ',' + th;
    } else {
      this.sparkPoints = '0,28 20,22 40,24 60,18 80,20 100,12 120,16 140,8';
      this.trendPoints = '0,120 40,100 80,110 120,70 160,85 200,50 240,60 280,40 320,55';
      this.trendArea = '0,160 ' + this.trendPoints + ' 320,160';
    }

    const pct = this.targetAchievedPct();
    const deg = Math.max(0, Math.min(360, (pct / 100) * 360));
    this.gaugeSafe = this.sanitizer.bypassSecurityTrustStyle(
      `conic-gradient(#18A957 0deg ${deg}deg, #DCE5EF ${deg}deg 360deg)`
    );
    const tasks = this.n(this.s().today_tasks_count);
    const doneDeg = tasks > 0 ? (this.tasksDone() / tasks) * 360 : 0;
    this.workSafe = this.sanitizer.bypassSecurityTrustStyle(
      `conic-gradient(#18A957 0deg ${doneDeg}deg, #D6E4FF ${doneDeg}deg 360deg)`
    );
    const attDeg = (this.attendancePct() / 100) * 360;
    this.attendSafe = this.sanitizer.bypassSecurityTrustStyle(
      `conic-gradient(#18A957 0deg ${attDeg}deg, #FFD6D6 ${attDeg}deg 360deg)`
    );
    this.fieldMapSafe = this.sanitizer.bypassSecurityTrustResourceUrl(
      'data:text/html;charset=utf-8,' + encodeURIComponent(this.fieldMapHtml())
    );

    const run = this.n(this.s().running_groups);
    const vac = this.n(this.s().vacant_groups);
    const tot = run + vac;
    if (tot > 0) {
      const a = (run / tot) * 360;
      const b = a + (vac / tot) * 360 * 0.7;
      const c = Math.min(360, b + 30);
      this.bizDonutSafe = this.sanitizer.bypassSecurityTrustStyle(
        `conic-gradient(#16a34a 0deg ${a}deg, #94a3b8 ${a}deg ${b}deg, #38bdf8 ${b}deg ${c}deg, #cbd5e1 ${c}deg 360deg)`
      );
    }
  }
}
