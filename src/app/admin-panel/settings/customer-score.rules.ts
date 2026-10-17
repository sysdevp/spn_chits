export const SCORE_START = 1000;
export const CUSTOMER_CODE_START = 1000;
export const PENDING_PENALTY_MAX = 400;
export const LATE_INSTALLMENT_POINTS = 20;
export const LATE_DAY_POINTS = 1;
export const LATE_DAYS_PER_INSTALLMENT = 10;
export const LATE_PENALTY_MAX = 400;

export interface ScoreBand {
  min: number;
  max: number;
  label: string;
  meaning: string;
}

export const SCORE_BANDS: ScoreBand[] = [
  { min: 850, max: 1000, label: 'Excellent', meaning: 'Payments are regular. Pending and late days are small.' },
  { min: 700, max: 849, label: 'Good', meaning: 'Most dues are paid. A few pending or late days remain.' },
  { min: 500, max: 699, label: 'Medium', meaning: 'A sizeable amount is pending, or several installments are late.' },
  { min: 0, max: 499, label: 'Bad', meaning: 'Pending is high and late days have reduced the score sharply.' }
];

export interface CustomerScoreInput {
  paid: number;
  pending: number;
  lateInstallments: number;
  lateDays: number;
}

export interface CustomerScoreResult {
  score: number;
  grade: string;
  meaning: string;
  pendingPenalty: number;
  latePenalty: number;
  countedLateDays: number;
  paid: number;
  pending: number;
  lateInstallments: number;
  lateDays: number;
}

export function scoreGrade(score: number): string {
  const band = SCORE_BANDS.filter((row) => score >= row.min && score <= row.max)[0] || SCORE_BANDS[SCORE_BANDS.length - 1];
  return band.label;
}

export function calculateCustomerScore(input: CustomerScoreInput): CustomerScoreResult {
  const paid = Math.max(0, Number(input.paid) || 0);
  const pending = Math.max(0, Number(input.pending) || 0);
  const lateInstallments = Math.max(0, Math.floor(Number(input.lateInstallments) || 0));
  const lateDays = Math.max(0, Math.floor(Number(input.lateDays) || 0));
  const countedLateDays = Math.min(lateDays, lateInstallments * LATE_DAYS_PER_INSTALLMENT);
  const base = paid + pending;
  const pendingPenalty = base > 0 ? Math.round((pending / base) * PENDING_PENALTY_MAX) : 0;
  let latePenalty = (lateInstallments * LATE_INSTALLMENT_POINTS) + (countedLateDays * LATE_DAY_POINTS);
  if (latePenalty > LATE_PENALTY_MAX) {
    latePenalty = LATE_PENALTY_MAX;
  }
  let score = SCORE_START - pendingPenalty - latePenalty;
  if (score < 0) {
    score = 0;
  }
  if (score > SCORE_START) {
    score = SCORE_START;
  }
  const band = SCORE_BANDS.filter((row) => score >= row.min && score <= row.max)[0] || SCORE_BANDS[SCORE_BANDS.length - 1];
  return {
    score: score,
    grade: band.label,
    meaning: band.meaning,
    pendingPenalty: pendingPenalty,
    latePenalty: latePenalty,
    countedLateDays: countedLateDays,
    paid: paid,
    pending: pending,
    lateInstallments: lateInstallments,
    lateDays: lateDays
  };
}
