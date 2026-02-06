import { Link } from 'react-router-dom';
import { TrashIcon, MinusIcon, PlusIcon, ShoppingBagIcon } from '@heroicons/react/24/outline';
import { useCart } from '../../context/CartContext';
import { formatCurrency, getImageUrl } from '../../utils/helpers';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import Spinner from '../../components/ui/Spinner';

export default function Cart() {
  const { items, loading, cartCount, subtotal, tax, deliveryFee, grandTotal, updateQuantity, removeItem, clearCartItems } = useCart();

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  if (items.length === 0) {
    return (
      <div className="container-app py-16">
        <EmptyState
          icon={ShoppingBagIcon}
          title="Your cart is empty"
          description="Browse our menu and add some delicious dishes!"
          action={<Link to="/menu"><Button>Browse Menu</Button></Link>}
        />
      </div>
    );
  }

  return (
    <div className="container-app py-8 sm:py-12">
      <div className="flex items-center justify-between mb-6">
        <h1 className="font-heading text-2xl font-bold text-stone-900 sm:text-3xl">Your Cart ({cartCount})</h1>
        <Button variant="ghost" size="sm" onClick={clearCartItems} className="text-red-500 hover:text-red-600">
          Clear All
        </Button>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {/* Cart Items */}
        <div className="lg:col-span-2 space-y-3">
          {items.map((item) => (
            <div key={item.dish_id} className="flex gap-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
              <img
                src={getImageUrl(item.image_path)}
                alt={item.name}
                className="h-20 w-20 sm:h-24 sm:w-24 shrink-0 rounded-lg object-cover bg-stone-100"
              />
              <div className="flex flex-1 flex-col min-w-0">
                <div className="flex items-start justify-between gap-2">
                  <h3 className="font-semibold text-stone-900 truncate">{item.name}</h3>
                  <button onClick={() => removeItem(item.dish_id)} className="shrink-0 rounded-lg p-1 text-stone-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                    <TrashIcon className="h-4 w-4" />
                  </button>
                </div>
                <p className="text-sm text-primary-600 font-medium">{formatCurrency(item.price)}</p>
                <div className="mt-auto flex items-center justify-between pt-2">
                  <div className="flex items-center gap-1 rounded-lg border border-stone-200">
                    <button
                      onClick={() => updateQuantity(item.dish_id, Math.max(1, item.quantity - 1))}
                      className="rounded-l-lg p-1.5 text-stone-500 hover:bg-stone-100 transition-colors"
                    >
                      <MinusIcon className="h-3.5 w-3.5" />
                    </button>
                    <span className="w-8 text-center text-sm font-medium text-stone-800">{item.quantity}</span>
                    <button
                      onClick={() => updateQuantity(item.dish_id, item.quantity + 1)}
                      className="rounded-r-lg p-1.5 text-stone-500 hover:bg-stone-100 transition-colors"
                    >
                      <PlusIcon className="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <span className="font-semibold text-stone-900">{formatCurrency(item.price * item.quantity)}</span>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Order Summary */}
        <div className="lg:col-span-1">
          <div className="sticky top-20 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h3 className="text-lg font-semibold text-stone-900 mb-4">Order Summary</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between text-stone-600"><span>Subtotal</span><span>{formatCurrency(subtotal)}</span></div>
              <div className="flex justify-between text-stone-600"><span>Tax (12%)</span><span>{formatCurrency(tax)}</span></div>
              <div className="flex justify-between text-stone-600"><span>Delivery Fee</span><span>{formatCurrency(deliveryFee)}</span></div>
              <div className="border-t border-stone-200 pt-2 flex justify-between font-semibold text-stone-900 text-base">
                <span>Total</span><span>{formatCurrency(grandTotal)}</span>
              </div>
            </div>
            <Link to="/checkout">
              <Button className="w-full mt-5">Proceed to Checkout</Button>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
