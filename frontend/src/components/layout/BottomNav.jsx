import { NavLink } from 'react-router-dom';
import { HomeIcon, Squares2X2Icon, ShoppingCartIcon, ClipboardDocumentListIcon, UserCircleIcon } from '@heroicons/react/24/outline';
import { HomeIcon as HomeIconSolid, Squares2X2Icon as Squares2X2IconSolid, ShoppingCartIcon as ShoppingCartIconSolid, ClipboardDocumentListIcon as ClipboardDocumentListIconSolid, UserCircleIcon as UserCircleIconSolid } from '@heroicons/react/24/solid';
import { useAuth } from '../../context/AuthContext';
import { useCart } from '../../context/CartContext';

const links = [
  { to: '/', label: 'Home', icon: HomeIcon, activeIcon: HomeIconSolid, end: true },
  { to: '/menu', label: 'Menu', icon: Squares2X2Icon, activeIcon: Squares2X2IconSolid },
  { to: '/cart', label: 'Cart', icon: ShoppingCartIcon, activeIcon: ShoppingCartIconSolid, auth: true },
  { to: '/orders', label: 'Orders', icon: ClipboardDocumentListIcon, activeIcon: ClipboardDocumentListIconSolid, auth: true },
  { to: '/profile', label: 'Profile', icon: UserCircleIcon, activeIcon: UserCircleIconSolid, auth: true },
];

const guestLinks = [
  { to: '/', label: 'Home', icon: HomeIcon, activeIcon: HomeIconSolid, end: true },
  { to: '/menu', label: 'Menu', icon: Squares2X2Icon, activeIcon: Squares2X2IconSolid },
  { to: '/sign-in', label: 'Sign In', icon: UserCircleIcon, activeIcon: UserCircleIconSolid },
];

export default function BottomNav() {
  const { isAuthenticated } = useAuth();
  const { cartCount } = useCart();
  const navLinks = isAuthenticated ? links : guestLinks;

  return (
    <nav className="fixed bottom-0 inset-x-0 z-40 border-t border-stone-200 bg-white/95 backdrop-blur-lg md:hidden safe-area-bottom">
      <div className="flex items-center justify-around h-14">
        {navLinks.map((l) => (
          <NavLink
            key={l.to}
            to={l.to}
            end={l.end}
            className={({ isActive }) =>
              `relative flex flex-col items-center justify-center gap-0.5 flex-1 py-1.5 text-[10px] font-medium transition-colors ${
                isActive ? 'text-primary-600' : 'text-stone-400'
              }`
            }
          >
            {({ isActive }) => (
              <>
                <div className="relative">
                  {isActive ? <l.activeIcon className="h-5 w-5" /> : <l.icon className="h-5 w-5" />}
                  {l.label === 'Cart' && cartCount > 0 && (
                    <span className="absolute -right-1.5 -top-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-primary-600 text-[8px] font-bold text-white">
                      {cartCount > 9 ? '9+' : cartCount}
                    </span>
                  )}
                </div>
                <span>{l.label}</span>
              </>
            )}
          </NavLink>
        ))}
      </div>
    </nav>
  );
}
