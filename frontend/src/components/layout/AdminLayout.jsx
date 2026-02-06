import { Outlet, NavLink, useNavigate } from 'react-router-dom';
import { Fragment, useState, useEffect, useRef, useCallback } from 'react';
import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';
import {
  Bars3Icon, XMarkIcon, HomeIcon, ClipboardDocumentListIcon,
  Squares2X2Icon, ArchiveBoxIcon, ChartBarIcon,
  InformationCircleIcon, UsersIcon, ArrowRightOnRectangleIcon, BellIcon,
  CheckIcon,
} from '@heroicons/react/24/outline';
import { useAuth } from '../../context/AuthContext';
import { getNotifications } from '../../api/admin';
import api from '../../api/axios';

const sidebarLinks = [
  { to: '/admin', icon: HomeIcon, label: 'Dashboard', end: true },
  { to: '/admin/menu', icon: Squares2X2Icon, label: 'Menu' },
  { to: '/admin/orders', icon: ClipboardDocumentListIcon, label: 'Orders' },
  { to: '/admin/inventory', icon: ArchiveBoxIcon, label: 'Inventory' },
  { to: '/admin/reports', icon: ChartBarIcon, label: 'Reports' },
  { to: '/admin/about', icon: InformationCircleIcon, label: 'About CMS' },
  { to: '/admin/accounts', icon: UsersIcon, label: 'Accounts', superOnly: true },
];

export default function AdminLayout() {
  const { user, logout, isSuperAdmin } = useAuth();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [notifOpen, setNotifOpen] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const notifRef = useRef(null);

  const visibleLinks = sidebarLinks.filter((l) => !l.superOnly || isSuperAdmin);

  const unreadCount = notifications.filter((n) => !n.read_at).length;

  const fetchNotifications = useCallback(async () => {
    try {
      const { data } = await getNotifications();
      setNotifications(data.data || []);
    } catch { /* ignore */ }
  }, []);

  useEffect(() => {
    fetchNotifications();
    const interval = setInterval(fetchNotifications, 30000);
    return () => clearInterval(interval);
  }, [fetchNotifications]);

  // Close notification dropdown on outside click
  useEffect(() => {
    const handler = (e) => {
      if (notifRef.current && !notifRef.current.contains(e.target)) setNotifOpen(false);
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  const markRead = async (id) => {
    try {
      await api.put(`/admin/notifications/${id}/read`);
      setNotifications((prev) => prev.map((n) => n.id === id ? { ...n, read_at: new Date().toISOString() } : n));
    } catch { /* ignore */ }
  };

  const markAllRead = async () => {
    try {
      await api.put('/admin/notifications/read-all');
      setNotifications((prev) => prev.map((n) => ({ ...n, read_at: n.read_at || new Date().toISOString() })));
    } catch { /* ignore */ }
  };

  const handleLogout = async () => {
    await logout();
    navigate('/sign-in');
  };

  const SidebarContent = ({ onClose }) => (
    <div className="flex h-full flex-col bg-stone-900">
      <div className="flex items-center gap-2.5 px-5 py-5 border-b border-stone-800">
        <div className="flex h-8 w-8 items-center justify-center rounded-full overflow-hidden shrink-0">
          <img src="/logo.jpg" alt="TK" className="h-8 w-8 rounded-full object-cover" />
        </div>
        <span className="font-heading text-base font-semibold text-white">Admin Panel</span>
      </div>

      <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        {visibleLinks.map((l) => (
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
          <div className="relative" ref={notifRef}>
            <button
              onClick={() => setNotifOpen(!notifOpen)}
              className="relative rounded-lg p-1.5 text-stone-500 hover:bg-stone-100"
            >
              <BellIcon className="h-5 w-5" />
              {unreadCount > 0 && (
                <span className="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white">
                  {unreadCount > 9 ? '9+' : unreadCount}
                </span>
              )}
            </button>
            {notifOpen && (
              <div className="absolute right-0 mt-2 w-80 rounded-xl border border-stone-200 bg-white shadow-lg z-50">
                <div className="flex items-center justify-between px-4 py-3 border-b border-stone-100">
                  <h3 className="text-sm font-semibold text-stone-900">Notifications</h3>
                  {unreadCount > 0 && (
                    <button onClick={markAllRead} className="text-xs text-primary-600 hover:text-primary-700 font-medium">
                      Mark all read
                    </button>
                  )}
                </div>
                <div className="max-h-72 overflow-y-auto">
                  {notifications.length === 0 ? (
                    <div className="px-4 py-8 text-center text-sm text-stone-400">No notifications</div>
                  ) : (
                    notifications.slice(0, 20).map((n) => (
                      <div
                        key={n.id}
                        className={`flex items-start gap-3 px-4 py-3 border-b border-stone-50 transition-colors ${!n.read_at ? 'bg-primary-50/50' : 'hover:bg-stone-50'}`}
                      >
                        <div className="flex-1 min-w-0">
                          <p className={`text-sm ${!n.read_at ? 'font-semibold text-stone-900' : 'text-stone-700'}`}>{n.title}</p>
                          <p className="text-xs text-stone-500 mt-0.5 line-clamp-2">{n.message}</p>
                        </div>
                        {!n.read_at && (
                          <button onClick={() => markRead(n.id)} className="shrink-0 rounded-md p-1 text-stone-400 hover:text-primary-600 hover:bg-primary-50">
                            <CheckIcon className="h-3.5 w-3.5" />
                          </button>
                        )}
                      </div>
                    ))
                  )}
                </div>
              </div>
            )}
          </div>
        </header>

        {/* Page content */}
        <main className="flex-1 overflow-y-auto p-4 lg:p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
