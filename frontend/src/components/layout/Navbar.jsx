import { Link, NavLink, useNavigate } from 'react-router-dom';
import { Fragment, useState } from 'react';
import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';
import { Bars3Icon, XMarkIcon, ShoppingCartIcon, UserCircleIcon } from '@heroicons/react/24/outline';
import { useAuth } from '../../context/AuthContext';
import { useCart } from '../../context/CartContext';

const navLinks = [
  { to: '/', label: 'Home' },
  { to: '/menu', label: 'Menu' },
  { to: '/about', label: 'About' },
  { to: '/orders', label: 'My Orders' },
];

const guestLinks = [
  { to: '/', label: 'Home' },
  { to: '/menu', label: 'Menu' },
  { to: '/about', label: 'About' },
];

export default function Navbar() {
  const { isAuthenticated, user, logout } = useAuth();
  const { cartCount } = useCart();
  const [mobileOpen, setMobileOpen] = useState(false);
  const navigate = useNavigate();
  const links = isAuthenticated ? navLinks : guestLinks;

  const handleLogout = async () => {
    await logout();
    navigate('/sign-in');
  };

  const avatarUrl = user?.avatar_url || null;

  return (
    <>
      <nav className="sticky top-0 z-40 border-b border-stone-200 bg-white/80 backdrop-blur-lg">
        <div className="container-app flex h-16 items-center justify-between">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2.5 shrink-0">
            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-sm">TK</div>
            <span className="hidden sm:block font-heading text-lg font-semibold text-stone-900">Tonishen's Kitchen</span>
          </Link>

          {/* Desktop Nav */}
          <div className="hidden md:flex items-center gap-1">
            {links.map((l) => (
              <NavLink
                key={l.to}
                to={l.to}
                end={l.to === '/'}
                className={({ isActive }) =>
                  `rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                    isActive ? 'bg-primary-50 text-primary-700' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'
                  }`
                }
              >
                {l.label}
              </NavLink>
            ))}
          </div>

          {/* Right side */}
          <div className="flex items-center gap-2">
            {isAuthenticated ? (
              <>
                <Link to="/cart" className="relative rounded-lg p-2 text-stone-600 hover:bg-stone-100 transition-colors">
                  <ShoppingCartIcon className="h-5 w-5" />
                  {cartCount > 0 && (
                    <span className="absolute -right-0.5 -top-0.5 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-primary-600 text-[10px] font-bold text-white">
                      {cartCount > 99 ? '99+' : cartCount}
                    </span>
                  )}
                </Link>
                <Link to="/profile" className="rounded-full overflow-hidden border-2 border-transparent hover:border-primary-300 transition-colors">
                  {avatarUrl ? (
                    <img src={avatarUrl} alt="" className="h-8 w-8 rounded-full object-cover" />
                  ) : (
                    <UserCircleIcon className="h-8 w-8 text-stone-400" />
                  )}
                </Link>
              </>
            ) : (
              <div className="hidden sm:flex items-center gap-2">
                <Link to="/sign-in" className="rounded-lg px-3 py-2 text-sm font-medium text-stone-600 hover:bg-stone-100 transition-colors">
                  Sign In
                </Link>
                <Link to="/sign-up" className="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                  Sign Up
                </Link>
              </div>
            )}

            {/* Mobile menu button */}
            <button
              onClick={() => setMobileOpen(true)}
              className="rounded-lg p-2 text-stone-600 hover:bg-stone-100 md:hidden transition-colors"
            >
              <Bars3Icon className="h-5 w-5" />
            </button>
          </div>
        </div>
      </nav>

      {/* Mobile Drawer */}
      <Transition show={mobileOpen} as={Fragment}>
        <Dialog onClose={setMobileOpen} className="relative z-50 md:hidden">
          <TransitionChild as={Fragment} enter="ease-out duration-300" enterFrom="opacity-0" enterTo="opacity-100" leave="ease-in duration-200" leaveFrom="opacity-100" leaveTo="opacity-0">
            <div className="fixed inset-0 bg-black/40" />
          </TransitionChild>
          <TransitionChild as={Fragment} enter="ease-out duration-300" enterFrom="translate-x-full" enterTo="translate-x-0" leave="ease-in duration-200" leaveFrom="translate-x-0" leaveTo="translate-x-full">
            <DialogPanel className="fixed inset-y-0 right-0 w-full max-w-xs bg-white shadow-xl p-6">
              <div className="flex items-center justify-between mb-8">
                <span className="font-heading text-lg font-semibold">Menu</span>
                <button onClick={() => setMobileOpen(false)} className="rounded-lg p-1 text-stone-400 hover:text-stone-600">
                  <XMarkIcon className="h-5 w-5" />
                </button>
              </div>
              <div className="flex flex-col gap-1">
                {links.map((l) => (
                  <NavLink
                    key={l.to}
                    to={l.to}
                    end={l.to === '/'}
                    onClick={() => setMobileOpen(false)}
                    className={({ isActive }) =>
                      `rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                        isActive ? 'bg-primary-50 text-primary-700' : 'text-stone-600 hover:bg-stone-100'
                      }`
                    }
                  >
                    {l.label}
                  </NavLink>
                ))}
                {isAuthenticated ? (
                  <button
                    onClick={() => { handleLogout(); setMobileOpen(false); }}
                    className="mt-4 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                  >
                    Sign Out
                  </button>
                ) : (
                  <div className="mt-4 flex flex-col gap-2">
                    <Link to="/sign-in" onClick={() => setMobileOpen(false)} className="rounded-lg border border-stone-300 px-3 py-2.5 text-center text-sm font-medium text-stone-700 hover:bg-stone-50">
                      Sign In
                    </Link>
                    <Link to="/sign-up" onClick={() => setMobileOpen(false)} className="rounded-lg bg-primary-600 px-3 py-2.5 text-center text-sm font-medium text-white hover:bg-primary-700">
                      Sign Up
                    </Link>
                  </div>
                )}
              </div>
            </DialogPanel>
          </TransitionChild>
        </Dialog>
      </Transition>
    </>
  );
}
