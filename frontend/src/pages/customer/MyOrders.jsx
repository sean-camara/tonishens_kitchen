import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ClipboardDocumentListIcon } from '@heroicons/react/24/outline';
import { getMyOrders } from '../../api/orders';
import { formatCurrency, formatDateTime } from '../../utils/helpers';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import EmptyState from '../../components/ui/EmptyState';

export default function MyOrders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const load = async () => {
      try {
        const { data } = await getMyOrders();
        setOrders(data.data || data);
      } catch { /* ignore */ }
      finally { setLoading(false); }
    };
    load();
    const interval = setInterval(load, 20000);
    return () => clearInterval(interval);
  }, []);

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="container-app py-8 sm:py-12">
      <h1 className="font-heading text-2xl font-bold text-stone-900 sm:text-3xl mb-6">My Orders</h1>

      {orders.length === 0 ? (
        <EmptyState
          icon={ClipboardDocumentListIcon}
          title="No orders yet"
          description="Time to order some delicious food!"
          action={<Link to="/menu"><Button>Browse Menu</Button></Link>}
        />
      ) : (
        <div className="space-y-3">
          {orders.map((order) => (
            <Link
              key={order.id}
              to={`/orders/${order.id}`}
              className="block rounded-xl border border-stone-200 bg-white p-4 sm:p-5 shadow-sm transition-all hover:shadow-md hover:border-stone-300"
            >
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div className="min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <span className="font-semibold text-stone-900">Order #{order.id}</span>
                    <Badge variant={order.status}>{order.status}</Badge>
                  </div>
                  <p className="text-sm text-stone-500">{formatDateTime(order.created_at || order.order_time)}</p>
                  {order.items && (
                    <p className="mt-1 text-sm text-stone-500 truncate">
                      {order.items.map((i) => `${i.dish_name || i.name} x${i.quantity}`).join(', ')}
                    </p>
                  )}
                </div>
                <div className="text-right shrink-0">
                  <p className="text-lg font-bold text-stone-900">{formatCurrency(order.total_amount)}</p>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
