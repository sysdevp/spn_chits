import axios from 'axios';
import { AuthUser } from '../types';

const baseURL = (
  process.env.EXPO_PUBLIC_API_BASE_URL ||
  'http://app.srispnchitfunds.com/backend/api'
).replace(/\/$/, '');

export const api = axios.create({
  baseURL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.message === 'Network Error' || error?.code === 'ERR_NETWORK') {
      error.message =
        'Cannot reach the server. Check internet and API URL: ' + baseURL;
    } else if (error?.code === 'ECONNABORTED') {
      error.message = 'Server timed out. Please try again.';
    } else if (error?.response?.data?.message) {
      error.message = String(error.response.data.message);
    }
    return Promise.reject(error);
  }
);

let authUser: AuthUser | null = null;

export function setApiAuthUser(user: AuthUser | null) {
  authUser = user;
  if (user?.token) {
    api.defaults.headers.common.Authorization = `Bearer ${user.token}`;
  } else {
    delete api.defaults.headers.common.Authorization;
  }
}

export function getApiAuthUser() {
  return authUser;
}

export function basePayload(user: AuthUser) {
  return {
    tenant_id: user.tenant_id,
    branch_id: user.branch_id,
    employee_id: user.employee_id,
    user_id: user.logged_user_id,
    db: user.db,
  };
}

export async function login(email: string, password: string) {
  const { data } = await api.post('mobile-login', { email, password });
  return data;
}

export async function fetchDashboard(user: AuthUser) {
  const { data } = await api.post('mobile-employee-dashboard', basePayload(user));
  return data;
}

export async function fetchCollectionCustomers(user: AuthUser) {
  const { data } = await api.post('mobile-get-collection-area-customers-new', basePayload(user));
  return data;
}

export async function fetchLeads(user: AuthUser) {
  const { data } = await api.post('mobile-list-leads', basePayload(user));
  return data;
}

export async function addLead(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('mobile-add-lead-management', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    ...payload,
  });
  return data;
}

export async function fetchTodayAttendance(user: AuthUser) {
  const { data } = await api.post('mobile-get-today-attendance', basePayload(user));
  return data;
}

export async function punchAttendance(
  user: AuthUser,
  punchType: 'check_in' | 'check_out',
  latitude: string,
  longitude: string
) {
  const { data } = await api.post('mobile-punch-attendance', {
    ...basePayload(user),
    punch_type: punchType,
    latitude,
    longitude,
    created_by: user.logged_user_id,
  });
  return data;
}

export async function fetchAppVersion() {
  const { data } = await api.post('mobile-app-version', {});
  return data;
}

export async function fetchFollowupStatusTypes(user: AuthUser) {
  const { data } = await api.post('list-followup-status-types', basePayload(user));
  return data;
}

export async function storeFollowup(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('store-followup-mobile', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    ...payload,
  });
  return data;
}

export async function fetchCustomerEnrollments(user: AuthUser, customerId: number) {
  const { data } = await api.post('mobile-get-customer-wise-entrolment-details-new', {
    ...basePayload(user),
    customer_id: customerId,
  });
  if (Array.isArray(data)) return data;
  if (Array.isArray(data?.data)) return data.data;
  return [];
}

export async function fetchPaymentTypes(user: AuthUser) {
  const { data } = await api.post('mobile-list-payment-types', basePayload(user));
  return Array.isArray(data) ? data : [];
}

export async function submitReceipt(user: AuthUser, payload: Record<string, unknown>) {
  const now = new Date();
  const receiptDate = now.toISOString().slice(0, 10);
  const receiptTime = now.toTimeString().slice(0, 8);
  const { data } = await api.post('mobile-add-receipt', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    employee_branch_id: user.branch_id,
    receipt_date: receiptDate,
    receipt_time: receiptTime,
    payment_type_id: 1,
    receipt_mode: 'Mobile',
    status: 1,
    ...payload,
  });
  return data;
}

export async function submitLeaveRequest(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('mobile-submit-leave-request', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    ...payload,
  });
  return data;
}

export async function fetchLeaveRequests(
  user: AuthUser,
  extra: Record<string, unknown> = {}
) {
  const payload = {
    tenant_id: user.tenant_id,
    branch_id: user.branch_id,
    user_id: user.logged_user_id,
    db: user.db,
    ...extra,
  };
  const { data } = await api.post('mobile-list-leave-requests', payload);
  return Array.isArray(data?.data) ? data.data : [];
}

export async function decideLeaveRequest(
  user: AuthUser,
  id: number,
  decision: 'approved' | 'rejected',
  remark = ''
) {
  const { data } = await api.post('mobile-decide-leave-request', {
    ...basePayload(user),
    id,
    decision,
    approval_remark: remark,
    approved_by: user.logged_user_id,
  });
  return data;
}

export async function fetchTodayTaskTargetSummary(user: AuthUser) {
  const { data } = await api.post('mobile-today-task-target-summary', {
    ...basePayload(user),
    employee_id: user.employee_id || 0,
  });
  return data;
}

export async function fetchEmployeeTasks(
  user: AuthUser,
  extra: Record<string, unknown> = {}
) {
  const { data } = await api.post('mobile-list-employee-tasks', {
    ...basePayload(user),
    ...extra,
  });
  return Array.isArray(data?.data) ? data.data : [];
}

export async function updateEmployeeTaskStatus(
  user: AuthUser,
  id: number,
  task_status: string,
  employee_remark = ''
) {
  const { data } = await api.post('mobile-update-employee-task-status', {
    ...basePayload(user),
    id,
    task_status,
    employee_remark,
    updated_by: user.logged_user_id,
  });
  return data;
}

export async function storeEmployeeTask(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('mobile-store-employee-task', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    assigned_by: user.logged_user_id,
    ...payload,
  });
  return data;
}

export async function fetchEmployeeTargets(
  user: AuthUser,
  extra: Record<string, unknown> = {}
) {
  const { data } = await api.post('mobile-list-employee-targets', {
    ...basePayload(user),
    ...extra,
  });
  return Array.isArray(data?.data) ? data.data : [];
}

export async function updateEmployeeTargetProgress(
  user: AuthUser,
  id: number,
  achieved_amount: number
) {
  const { data } = await api.post('mobile-update-employee-target-progress', {
    ...basePayload(user),
    id,
    achieved_amount,
    updated_by: user.logged_user_id,
  });
  return data;
}

export async function storeEmployeeTarget(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('mobile-store-employee-target', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    ...payload,
  });
  return data;
}

export async function fetchMobileEmployees(user: AuthUser) {
  const { data } = await api.post('mobile-list-employees', {
    tenant_id: user.tenant_id,
    branch_id: user.branch_id,
    db: user.db,
  });
  return Array.isArray(data?.data) ? data.data : [];
}

export async function fetchEmployeePerformance(user: AuthUser, periodMonth?: string) {
  const { data } = await api.post('mobile-get-employee-performance', {
    ...basePayload(user),
    period_month: periodMonth,
  });
  return data;
}

export async function fetchEmployeeAchievements(user: AuthUser, periodMonth?: string) {
  const { data } = await api.post('mobile-get-employee-achievements', {
    ...basePayload(user),
    period_month: periodMonth,
  });
  return data;
}

export async function fetchLeaderboard(user: AuthUser, periodMonth?: string) {
  const { data } = await api.post('mobile-get-leaderboard', {
    ...basePayload(user),
    period_month: periodMonth,
  });
  return data;
}

export async function fetchConversionStats(user: AuthUser) {
  const { data } = await api.post('mobile-get-conversion-stats', { ...basePayload(user) });
  return data;
}

export async function fetchWorkPlans(user: AuthUser, extra: Record<string, unknown> = {}) {
  const { data } = await api.post('mobile-list-work-plans', { ...basePayload(user), ...extra });
  return Array.isArray(data?.data) ? data.data : [];
}

export async function storeWorkPlan(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('mobile-store-work-plan', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    ...payload,
  });
  return data;
}

export async function updateWorkPlanStatus(user: AuthUser, id: number, plan_status: string) {
  const { data } = await api.post('mobile-update-work-plan-status', {
    ...basePayload(user),
    id,
    plan_status,
    updated_by: user.logged_user_id,
  });
  return data;
}

export async function fetchCustomerVisits(user: AuthUser, extra: Record<string, unknown> = {}) {
  const { data } = await api.post('mobile-list-customer-visits', { ...basePayload(user), ...extra });
  return Array.isArray(data?.data) ? data.data : [];
}

export async function storeCustomerVisit(user: AuthUser, payload: Record<string, unknown>) {
  const { data } = await api.post('mobile-store-customer-visit', {
    ...basePayload(user),
    created_by: user.logged_user_id,
    ...payload,
  });
  return data;
}
