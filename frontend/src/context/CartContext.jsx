import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { getCart as fetchCartApi, addToCart as addApi, updateCartItem as updateApi, removeFromCart as removeApi, clearCart as clearApi } from '../api/cart';
import { useAuth } from './AuthContext';
import toast from 'react-hot-toast';

const CartContext = createContext(null);

export function CartProvider({ children }) {
  const { isAuthenticated, isAdmin } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);

  const fetchCart = useCallback(async () => {
    if (!isAuthenticated || isAdmin) return;
    setLoading(true);
    try {
      const { data } = await fetchCartApi();
      setItems(data.items || []);
    } catch { /* ignore */ }
    finally { setLoading(false); }
  }, [isAuthenticated, isAdmin]);

  useEffect(() => { fetchCart(); }, [fetchCart]);

  const addToCart = async (dishId, qty = 1) => {
    try {
      await addApi(dishId, qty);
      await fetchCart();
      toast.success('Added to cart');
    } catch (err) {
      if (err.response?.status !== 422) toast.error('Failed to add to cart');
    }
  };

  const updateQuantity = async (dishId, qty) => {
    try {
      await updateApi(dishId, qty);
      await fetchCart();
    } catch { toast.error('Failed to update quantity'); }
  };

  const removeItem = async (dishId) => {
    try {
      await removeApi(dishId);
      setItems((prev) => prev.filter((i) => i.dish_id !== dishId));
      toast.success('Removed from cart');
    } catch { toast.error('Failed to remove item'); }
  };

  const clearCartItems = async () => {
    try {
      await clearApi();
      setItems([]);
    } catch { toast.error('Failed to clear cart'); }
  };

  const cartCount = items.reduce((sum, i) => sum + i.quantity, 0);
  const subtotal = items.reduce((sum, i) => sum + i.price * i.quantity, 0);
  const tax = subtotal * 0.12;
  const deliveryFee = items.length > 0 ? 50 : 0;
  const grandTotal = subtotal + tax + deliveryFee;

  return (
    <CartContext.Provider value={{
      items, loading, cartCount, subtotal, tax, deliveryFee, grandTotal,
      addToCart, updateQuantity, removeItem, clearCartItems, fetchCart,
    }}>
      {children}
    </CartContext.Provider>
  );
}

export const useCart = () => {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within CartProvider');
  return ctx;
};
