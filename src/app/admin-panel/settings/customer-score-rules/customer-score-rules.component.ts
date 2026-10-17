import { Component, OnInit } from '@angular/core';
import {
  CUSTOMER_CODE_START,
  LATE_DAY_POINTS,
  LATE_DAYS_PER_INSTALLMENT,
  LATE_INSTALLMENT_POINTS,
  LATE_PENALTY_MAX,
  PENDING_PENALTY_MAX,
  SCORE_BANDS,
  SCORE_START,
  calculateCustomerScore,
  CustomerScoreResult
} from '../customer-score.rules';

@Component({
  selector: 'app-customer-score-rules',
  templateUrl: './customer-score-rules.component.html',
  styles: [`
    .score-guide { max-width: 1080px; color: #10233f; }
    .guide-hero, .flow-step, .grade-card, .rule-card, .example, .code-card {
      background: #fff; border-radius: 16px; border: 1px solid rgba(7, 29, 58, .06);
      box-shadow: 0 8px 22px rgba(7, 29, 58, .06);
    }
    .guide-hero {
      display: flex; justify-content: space-between; gap: 18px; align-items: center; flex-wrap: wrap;
      padding: 22px 26px; margin-bottom: 14px;
    }
    .eyebrow { margin: 0 0 6px; letter-spacing: .08em; text-transform: uppercase; font-size: 11px; color: #18A957; font-weight: 700; }
    .guide-hero h2 { margin: 0 0 8px; font-size: 26px; color: #071D3A; }
    .guide-hero p { margin: 0; max-width: 640px; color: #5c6b80; }
    .hero-score {
      min-width: 112px; height: 112px; border-radius: 50%; background: #E9FFF3; color: #18A957;
      border: 6px solid #fff; box-shadow: 0 0 0 3px #18A957;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .hero-score strong { font-size: 30px; line-height: 1; }
    .hero-score span { font-size: 11px; color: #5c6b80; margin-top: 4px; }
    .flow { display: flex; align-items: stretch; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
    .flow-step { flex: 1 1 160px; padding: 14px 16px; min-height: 108px; }
    .flow-step span { display: block; font-size: 12px; color: #5c6b80; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; }
    .flow-step strong { display: block; font-size: 24px; margin: 4px 0; color: #071D3A; }
    .flow-step small { display: block; color: #6d7c90; }
    .flow-step.navy { background: #EDF6FF; }
    .flow-step.amber { background: #FFF8E8; }
    .flow-step.red { background: #FFF0F4; }
    .flow-step.green { background: #E9FFF3; }
    .flow-arrow { align-self: center; font-size: 26px; font-weight: 800; color: #1687E8; }
    .section-title { margin: 8px 0 10px; color: #071D3A; font-size: 18px; font-weight: 800; }
    .grade-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
    .grade-card { padding: 16px; min-height: 148px; color: #10233f; }
    .grade-card h3 { margin: 8px 0 6px; font-size: 20px; color: #071D3A; }
    .grade-card p { margin: 0; font-size: 13px; line-height: 1.45; color: #5c6b80; }
    .grade-range { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 700; color: #fff; }
    .grade-card.excellent { background: #E9FFF3; }
    .grade-card.excellent .grade-range, .grade-card.excellent h3 { color: #0d7a45; }
    .grade-card.excellent .grade-range { background: #18A957; color: #fff; }
    .grade-card.good { background: #EDF6FF; }
    .grade-card.good h3 { color: #1565b8; }
    .grade-card.good .grade-range { background: #1687E8; }
    .grade-card.medium { background: #FFF8E8; }
    .grade-card.medium h3 { color: #9a6b00; }
    .grade-card.medium .grade-range { background: #F2A900; color: #3f2d00; }
    .grade-card.bad { background: #FFF0F4; }
    .grade-card.bad h3 { color: #c2185b; }
    .grade-card.bad .grade-range { background: #EF476F; }
    .meter { display: flex; height: 14px; border-radius: 999px; overflow: hidden; margin-top: 14px; }
    .meter div { font-size: 0; }
    .meter-bad { width: 50%; background: #EF476F; }
    .meter-medium { width: 20%; background: #F2A900; }
    .meter-good { width: 15%; background: #1687E8; }
    .meter-excellent { width: 15%; background: #18A957; }
    .meter-scale { display: flex; justify-content: space-between; color: #6d7c90; font-size: 12px; margin: 4px 0 18px; }
    .rule-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .rule-card { padding: 16px; }
    .rule-card h3 { margin: 10px 0 6px; color: #071D3A; font-size: 16px; }
    .rule-card p, .rule-card li { color: #5c6b80; font-size: 14px; line-height: 1.45; }
    .rule-icon { display: inline-flex; align-items: center; justify-content: center; min-width: 72px; height: 32px; border-radius: 9px; color: #fff; font-weight: 800; padding: 0 12px; font-size: 13px; }
    .rule-card.paid { background: #E9FFF3; }
    .rule-card.paid .rule-icon { background: #18A957; }
    .rule-card.pending { background: #FFF8E8; }
    .rule-card.pending .rule-icon { background: #F2A900; color: #3f2d00; }
    .rule-card.late { background: #FFF0F4; }
    .rule-card.late .rule-icon { background: #EF476F; }
    .enroll-note { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
    .mini-enrol, .mini-customer { border-radius: 12px; padding: 10px 14px; font-weight: 700; }
    .mini-enrol { background: #EDF6FF; color: #1565b8; }
    .mini-customer { background: #E9FFF3; color: #0d7a45; border: 1px solid #b7ebc9; }
    .mini-plus { font-weight: 800; color: #1687E8; }
    .enroll-copy { color: #5c6b80; margin: 8px 0 16px; }
    .example { padding: 16px; margin-bottom: 16px; }
    .example-inputs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .example-inputs div { background: #F4F7FB; border-radius: 12px; padding: 10px 12px; }
    .example-inputs span { display: block; color: #6d7c90; font-size: 12px; }
    .example-inputs strong { color: #071D3A; font-size: 18px; }
    .example-math { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 14px 0; }
    .math-pill { min-width: 72px; border-radius: 12px; padding: 10px 12px; font-weight: 800; text-align: center; color: #071D3A; }
    .math-pill.navy { background: #EDF6FF; color: #1565b8; }
    .math-pill.amber { background: #FFF8E8; color: #9a6b00; }
    .math-pill.red { background: #FFF0F4; color: #c2185b; }
    .math-pill.result { background: #E9FFF3; color: #0d7a45; }
    .math-pill.result b { display: block; font-size: 22px; }
    .math-pill.result small { font-weight: 700; }
    .math-pill.grade-excellent { background: #E9FFF3; color: #0d7a45; }
    .math-pill.grade-good { background: #EDF6FF; color: #1565b8; }
    .math-pill.grade-medium { background: #FFF8E8; color: #9a6b00; }
    .math-pill.grade-bad { background: #FFF0F4; color: #c2185b; }
    .example p { margin: 0; color: #5c6b80; }
    .code-card { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 16px; }
    .code-chip { background: #071D3A; color: #fff; border-radius: 10px; padding: 10px 14px; font-weight: 800; }
    .code-chip.branch { background: #1687E8; }
    .code-chip.num { background: #F5B700; color: #5c3b00; }
    .code-dash { font-size: 22px; font-weight: 800; color: #9aa8b8; }
    .code-copy { flex: 1 1 240px; }
    .code-copy p { margin: 0 0 6px; color: #5c6b80; }
    @media (max-width: 900px) {
      .grade-row, .rule-grid, .example-inputs { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 640px) {
      .grade-row, .rule-grid, .example-inputs { grid-template-columns: 1fr; }
      .flow-arrow { display: none; }
    }
  `]
})
export class CustomerScoreRulesComponent implements OnInit {
  start = SCORE_START;
  codeStart = CUSTOMER_CODE_START;
  pendingMax = PENDING_PENALTY_MAX;
  lateInstallmentPoints = LATE_INSTALLMENT_POINTS;
  lateDayPoints = LATE_DAY_POINTS;
  lateDaysCap = LATE_DAYS_PER_INSTALLMENT;
  lateMax = LATE_PENALTY_MAX;
  bands = SCORE_BANDS;
  example: CustomerScoreResult;

  ngOnInit() {
    this.example = calculateCustomerScore({
      paid: 80000,
      pending: 20000,
      lateInstallments: 2,
      lateDays: 14
    });
  }

  gradeClass(label: string): string {
    return 'grade-' + (label || '').toLowerCase();
  }
}
