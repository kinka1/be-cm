import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { BarChart3, Boxes, ClipboardList, FolderTree, LayoutDashboard, LogOut, Menu, Package, ShoppingCart, Users } from 'lucide-react';
import clsx from 'clsx';
import { useState } from 'react';
import { useAuth } from '../lib/auth';
import { Button } from '../components/ui';

const navItems = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/pos', label: 'POS Kasir', icon: ShoppingCart },
  { to: '/orders', label: 'Orders', icon: ClipboardList },
  { to: '/products', label: 'Products', icon: Package },
  { to: '/categories', label: 'Categories', icon: FolderTree },
  { to: '/stock', label: 'Stock', icon: Boxes },
  { to: '/employees', label: 'Employees', icon: Users },
];

export function AppLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/login', { replace: true });
  };

  return (
    <div className="min-h-screen bg-surface text-ink lg:grid lg:grid-cols-[260px_1fr]">
      <aside className={clsx('fixed inset-y-0 left-0 z-40 w-64 border-r border-line bg-white transition lg:static lg:translate-x-0', open ? 'translate-x-0' : '-translate-x-full')}>
        <div className="flex h-16 items-center gap-3 border-b border-line px-5">
          <div className="flex h-10 w-10 items-center justify-center rounded-md bg-brand text-white"><BarChart3 className="h-5 w-5" /></div>
          <div>
            <p className="font-bold leading-tight">POS Management</p>
            <p className="text-xs text-muted">Kasir dan stok</p>
          </div>
        </div>
        <nav className="grid gap-1 p-3">
          {navItems.map((item) => {
            const Icon = item.icon;
            return (
              <NavLink key={item.to} to={item.to} onClick={() => setOpen(false)} className={({ isActive }) => clsx('flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold', isActive ? 'bg-teal-50 text-brand' : 'text-muted hover:bg-slate-100 hover:text-ink')}>
                <Icon className="h-4 w-4" />{item.label}
              </NavLink>
            );
          })}
        </nav>
      </aside>
      {open && <button className="fixed inset-0 z-30 bg-black/30 lg:hidden" onClick={() => setOpen(false)} aria-label="Close menu" />}
      <div className="min-w-0">
        <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-line bg-white/95 px-4 backdrop-blur lg:px-6">
          <button className="rounded-md p-2 hover:bg-slate-100 lg:hidden" onClick={() => setOpen(true)} aria-label="Open menu"><Menu className="h-5 w-5" /></button>
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-ink">{user?.name ?? 'Operator'}</p>
            <p className="truncate text-xs text-muted">Employee ID: {user?.employee_id ?? 'belum tersedia'}</p>
          </div>
          <Button variant="secondary" onClick={handleLogout}><LogOut className="h-4 w-4" />Logout</Button>
        </header>
        <main className="mx-auto w-full max-w-[1500px] p-4 lg:p-6"><Outlet /></main>
      </div>
    </div>
  );
}
