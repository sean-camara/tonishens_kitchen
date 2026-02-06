import { Outlet, NavLink, useNavigate } from 'react-router-dom';
import { Fragment, useState } from 'react';
import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';
import {
  Bars3Icon, XMarkIcon, HomeIcon, ClipboardDocumentListIcon,
  Squares2X2Icon, ArchiveBoxIcon, ChartBarIcon,
  InformationCircleIcon, UsersIcon, ArrowRightOnRectangleIcon, BellIcon,
} from '@heroicons/react/24/outline';
import { useAuth } from '../../context/AuthContext';

const sidebarLinks = [
  { to: '/admin', icon: HomeIcon, label: 'Dashboard', end: true },
  { to: '/admin/menu', icon: Squares2X2Icon, label: 'Menu' },
  { to: '/admin/orders', icon: ClipboardDocumentListIcon, label: 'Orders' },
  { to: '/admin/inventory', icon: ArchiveBoxIcon, label: 'Inventory' },
  { to: '/admin/reports', icon: ChartBarIcon, label: 'Reports' },
  { to: '/admin/about', icon: InformationCircleIcon, label: 'About CMS' },
  { to: '/admin/accounts', icon: UsersIcon, label: 'Accounts' },
];

export default function AdminLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/sign-in');
  };

  const SidebarContent = ({ onClose }) => (
    <div className="flex h-full flex-col bg-stone-900">
      <div className="flex items-center gap-2.5 px-5 py-5 border-b border-stone-800">
        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-xs">TK</div>
        <span className="font-heading text-base font-semibold text-white">Admin Panel</span>
      </div>

      <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        {sidebarLinks.map((l) => (
          <NavLink
            key={l.to}
            to={l.to}
            end={l.end}
            onClick={onClose}
            className={({ isActive }) =>
              `flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                isActive ? 'bg-primary-600/20 text-primary-400' : 'text-stone-400 hover:bg-stone-800 hover:text-white'
              }`
            }
          >
            <l.icon className="h-5 w-5 shrink-0" />
            {l.label}
          </NavLink>
        ))}
      </nav>

      <div className="border-t border-stone-800 p-3">
        <div className="mb-2 flex items-center gap-2 px-3 py-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-stone-700 text-xs font-medium text-stone-300">
            {user?.first_name?.[0]}{user?.last_name?.[0]}
          </div>
          <div className="min-w-0">
            <p className="truncate text-sm font-medium text-white">{user?.first_name} {user?.last_name}</p>
            <p className="truncate text-xs text-stone-500">{user?.email}</p>
          </div>
        </div>
        <button
          onClick={handleLogout}
          className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-stone-400 hover:bg-stone-800 hover:text-red-400 transition-colors"
        >
          <ArrowRightOnRectangleIcon className="h-5 w-5" />
          Sign Out
        </button>
      </div>
    </div>
  );

  return (
    <div className="flex h-screen bg-stone-100">
      {/* Desktop sidebar */}
      <aside className="hidden lg:flex w-64 shrink-0 flex-col">
        <SidebarContent onClose={() => {}} />
      </aside>

      {/* Mobile sidebar */}
      <Transition show={sidebarOpen} as={Fragment}>
        <Dialog onClose={setSidebarOpen} className="relative z-50 lg:hidden">
          <TransitionChild as={Fragment} enter="ease-out duration-300" enterFrom="opacity-0" enterTo="opacity-100" leave="ease-in duration-200" leaveFrom="opacity-100" leaveTo="opacity-0">
            <div className="fixed inset-0 bg-black/40" />
          </TransitionChild>
          <TransitionChild as={Fragment} enter="ease-out duration-300" enterFrom="-translate-x-full" enterTo="translate-x-0" leave="ease-in duration-200" leaveFrom="translate-x-0" leaveTo="-translate-x-full">
            <DialogPanel className="fixed inset-y-0 left-0 w-64 shadow-xl">
              <SidebarContent onClose={() => setSidebarOpen(false)} />
            </DialogPanel>
          </TransitionChild>
        </Dialog>
      </Transition>

      {/* Main content */}
      <div className="flex flex-1 flex-col overflow-hidden">
        {/* Top bar */}
        <header className="flex h-14 items-center gap-3 border-b border-stone-200 bg-white px-4 lg:px-6">
          <button onClick={() => setSidebarOpen(true)} className="rounded-lg p-1.5 text-stone-500 hover:bg-stone-100 lg:hidden">
            <Bars3Icon className="h-5 w-5" />
          </button>
          <div className="flex-1" />
          <button className="relative rounded-lg p-1.5 text-stone-500 hover:bg-stone-100">
            <BellIcon className="h-5 w-5" />
          </button>
        </header>

        {/* Page content */}
        <main className="flex-1 overflow-y-auto p-4 lg:p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
