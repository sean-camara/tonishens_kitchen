import { useState, useEffect } from 'react';
import { CurrencyDollarIcon, ShoppingBagIcon, FireIcon, ExclamationTriangleIcon, StarIcon } from '@heroicons/react/24/outline';
import { getDashboard } from '../../api/admin';
import { formatCurrency, formatDateTime } from '../../utils/helpers';
import Card from '../../components/ui/Card';
import Badge from '../../components/ui/Badge';
import Spinner from '../../components/ui/Spinner';
import StarRating from '../../components/ui/StarRating';

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboard().then(({ data }) => setData(data)).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  const stats = [
    { label: 'Total Sales', value: formatCurrency(data?.total_sales || 0), icon: CurrencyDollarIcon, color: 'bg-emerald-100 text-emerald-600' },
    { label: 'Total Orders', value: data?.total_orders || 0, icon: ShoppingBagIcon, color: 'bg-blue-100 text-blue-600' },
    { label: 'Best Seller', value: data?.best_seller || 'N/A', icon: FireIcon, color: 'bg-orange-100 text-orange-600' },
    { label: 'Pending Orders', value: data?.pending_orders || 0, icon: ExclamationTriangleIcon, color: 'bg-yellow-100 text-yellow-600' },
  ];

  return (
    <div className="space-y-6">
      <h1 className="font-heading text-2xl font-bold text-stone-900">Dashboard</h1>

      {/* Stats */}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {stats.map((s) => (
          <Card key={s.label}>
            <Card.Body className="flex items-center gap-4">
              <div className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${s.color}`}>
                <s.icon className="h-6 w-6" />
              </div>
              <div className="min-w-0">
                <p className="text-xs font-medium uppercase tracking-wider text-stone-500">{s.label}</p>
                <p className="mt-0.5 text-xl font-bold text-stone-900 truncate">{s.value}</p>
              </div>
            </Card.Body>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {/* Recent Orders */}
        <Card>
          <Card.Header><h2 className="font-semibold text-stone-900">Recent Orders</h2></Card.Header>
          <Card.Body className="space-y-3">
            {(data?.recent_orders || []).slice(0, 5).map((order) => (
              <div key={order.id} className="flex items-center justify-between gap-3 rounded-lg border border-stone-100 p-3">
                <div className="min-w-0">
                  <p className="text-sm font-medium text-stone-800">Order #{order.id}</p>
                  <p className="text-xs text-stone-500">{formatDateTime(order.created_at || order.order_time)}</p>
                </div>
                <div className="flex items-center gap-2 shrink-0">
                  <Badge variant={order.status}>{order.status}</Badge>
                  <span className="text-sm font-semibold text-stone-800">{formatCurrency(order.total_amount)}</span>
                </div>
              </div>
            ))}
            {(!data?.recent_orders || data.recent_orders.length === 0) && (
              <p className="text-sm text-stone-500 text-center py-4">No recent orders</p>
            )}
          </Card.Body>
        </Card>

        {/* Low Stock + Feedback */}
        <div className="space-y-6">
          <Card>
            <Card.Header><h2 className="font-semibold text-stone-900">Low Stock Items</h2></Card.Header>
            <Card.Body className="space-y-2">
              {(data?.low_stock || []).map((item) => (
                <div key={item.id || item.item_name} className="flex items-center justify-between rounded-lg border border-orange-100 bg-orange-50 p-3">
                  <span className="text-sm font-medium text-stone-800">{item.item_name || item.name}</span>
                  <span className="text-sm font-bold text-orange-600">{item.stock_count ?? item.quantity} left</span>
                </div>
              ))}
              {(!data?.low_stock || data.low_stock.length === 0) && (
                <p className="text-sm text-stone-500 text-center py-4">All items in stock</p>
              )}
            </Card.Body>
          </Card>

          {data?.latest_feedback && (
            <Card>
              <Card.Header><h2 className="font-semibold text-stone-900">Latest Feedback</h2></Card.Header>
              <Card.Body>
                <div className="flex items-start gap-3">
                  <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600 font-bold text-sm">
                    {data.latest_feedback.user_name?.[0] || '?'}
                  </div>
                  <div>
                    <p className="text-sm font-medium text-stone-800">{data.latest_feedback.user_name}</p>
                    <StarRating rating={data.latest_feedback.rating} readonly size="sm" />
                    <p className="mt-1 text-sm text-stone-600">{data.latest_feedback.comment}</p>
                  </div>
                </div>
              </Card.Body>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
}
