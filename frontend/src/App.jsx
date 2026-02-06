import { Routes, Route } from 'react-router-dom';
import { ProtectedRoute, AdminRoute, GuestRoute } from './guards/RouteGuards';
import CustomerLayout from './components/layout/CustomerLayout';
import AdminLayout from './components/layout/AdminLayout';

// Public / Auth pages
import Landing from './pages/public/Landing';
import MenuPage from './pages/public/MenuPage';
import AboutPage from './pages/public/AboutPage';
import SignIn from './pages/public/SignIn';
import SignUp from './pages/public/SignUp';

// Customer pages
import Cart from './pages/customer/Cart';
import Checkout from './pages/customer/Checkout';
import MyOrders from './pages/customer/MyOrders';
import OrderDetail from './pages/customer/OrderDetail';
import Profile from './pages/customer/Profile';

// Admin pages
import Dashboard from './pages/admin/Dashboard';
import AdminMenu from './pages/admin/AdminMenu';
import AdminOrders from './pages/admin/AdminOrders';
import AdminOrderDetail from './pages/admin/AdminOrderDetail';
import Inventory from './pages/admin/Inventory';
import Reports from './pages/admin/Reports';
import AdminAbout from './pages/admin/AdminAbout';
import Accounts from './pages/admin/Accounts';

export default function App() {
  return (
    <Routes>
      {/* Guest-only routes */}
      <Route element={<GuestRoute />}>
        <Route path="/sign-in" element={<SignIn />} />
        <Route path="/sign-up" element={<SignUp />} />
      </Route>

      {/* Public + Customer routes with Navbar/Footer */}
      <Route element={<CustomerLayout />}>
        <Route path="/" element={<Landing />} />
        <Route path="/menu" element={<MenuPage />} />
        <Route path="/about" element={<AboutPage />} />

        {/* Protected customer routes */}
        <Route element={<ProtectedRoute />}>
          <Route path="/cart" element={<Cart />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="/orders" element={<MyOrders />} />
          <Route path="/orders/:id" element={<OrderDetail />} />
          <Route path="/profile" element={<Profile />} />
        </Route>
      </Route>

      {/* Admin routes */}
      <Route element={<AdminRoute />}>
        <Route element={<AdminLayout />}>
          <Route path="/admin" element={<Dashboard />} />
          <Route path="/admin/menu" element={<AdminMenu />} />
          <Route path="/admin/orders" element={<AdminOrders />} />
          <Route path="/admin/orders/:id" element={<AdminOrderDetail />} />
          <Route path="/admin/inventory" element={<Inventory />} />
          <Route path="/admin/reports" element={<Reports />} />
          <Route path="/admin/about" element={<AdminAbout />} />
          <Route path="/admin/accounts" element={<Accounts />} />
        </Route>
      </Route>
    </Routes>
  );
}
