import { Link } from 'react-router-dom';

export default function Footer() {
  return (
    <footer className="border-t border-stone-200 bg-stone-900 text-stone-300">
      <div className="container-app py-10">
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
          {/* Brand */}
          <div>
            <div className="flex items-center gap-2 mb-3">
              <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-xs">TK</div>
              <span className="font-heading text-lg font-semibold text-white">Tonishen's Kitchen</span>
            </div>
            <p className="text-sm text-stone-400 leading-relaxed">
              Homemade Filipino flavors made with passion. Every bite feels like home.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="mb-3 text-sm font-semibold text-white">Quick Links</h4>
            <ul className="space-y-2 text-sm">
              <li><Link to="/menu" className="hover:text-primary-400 transition-colors">Menu</Link></li>
              <li><Link to="/about" className="hover:text-primary-400 transition-colors">About Us</Link></li>
              <li><Link to="/orders" className="hover:text-primary-400 transition-colors">My Orders</Link></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="mb-3 text-sm font-semibold text-white">Contact</h4>
            <ul className="space-y-2 text-sm text-stone-400">
              <li>tonishen@gmail.com</li>
              <li>+63 917 123 4567</li>
              <li>13 Chestnut St, Novaliches, QC</li>
            </ul>
          </div>

          {/* Hours */}
          <div>
            <h4 className="mb-3 text-sm font-semibold text-white">Hours</h4>
            <ul className="space-y-2 text-sm text-stone-400">
              <li>Mon - Sat: 10AM - 9PM</li>
              <li>Sunday: 11AM - 8PM</li>
            </ul>
          </div>
        </div>

        <div className="mt-8 border-t border-stone-800 pt-6 text-center text-xs text-stone-500">
          &copy; {new Date().getFullYear()} Tonishen's Kitchen. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
