/** Head office / admin logins: role type name mentions admin or MD, or the login has no branch scope. */
export function isAdminLogin(): boolean {
  const role = (localStorage.getItem('role_type_name') || '').toLowerCase();
  if (/admin|\bmd\b|managing|director|super|head office|corporate/.test(role)) {
    return true;
  }
  const branchId = localStorage.getItem('branch_id');
  return !branchId || branchId === '0' || branchId === 'null';
}

export function isAccountsLogin(): boolean {
  const role = (localStorage.getItem('role_type_name') || '').toLowerCase();
  return /account/.test(role) || isAdminLogin();
}
