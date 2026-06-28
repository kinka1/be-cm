import { useQuery } from '@tanstack/react-query';
import { employeesApi } from '../api/endpoints';
import { getApiError } from '../api/client';
import { Badge } from '../components/ui';
import { EmptyState, ErrorState, LoadingState } from '../components/states';

export function EmployeesPage() {
  const employees = useQuery({ queryKey: ['employees'], queryFn: () => employeesApi.list() });
  return <section className="grid gap-4"><div><h1 className="text-2xl font-bold">Employees</h1><p className="text-sm text-muted">Endpoint tersedia sebagai /api/employees. CRUD create/update memakai upload dokumen, sehingga layar ini dibuat read-only dulu.</p></div>{employees.isLoading && <LoadingState />}{employees.error && <ErrorState message={getApiError(employees.error)} />}{!employees.isLoading && employees.data?.data.length === 0 && <EmptyState title="Employee kosong" />}<div className="overflow-hidden rounded-md border border-line bg-white"><div className="overflow-x-auto"><table className="w-full min-w-[720px] text-left text-sm"><thead className="bg-slate-50 text-xs uppercase text-muted"><tr><th className="px-4 py-3">Nama</th><th className="px-4 py-3">Email</th><th className="px-4 py-3">Role ID</th><th className="px-4 py-3">Status</th></tr></thead><tbody>{employees.data?.data.map((employee) => <tr key={employee.id} className="border-t border-line"><td className="px-4 py-3 font-semibold">{employee.full_name}</td><td className="px-4 py-3">{employee.email}</td><td className="px-4 py-3">{employee.role_id}</td><td className="px-4 py-3"><Badge tone={employee.status === 'active' ? 'green' : 'slate'}>{employee.status}</Badge></td></tr>)}</tbody></table></div></div></section>;
}
