import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getAdminOrder, updateOrderStatus } from '../../api/admin';
import { formatCurrency, formatDateTime, getImageUrl } from '../../utils/helpers';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import toast from 'react-hot-toast';

const statusOptions = ['Pending', 'Preparing', 'On the Way', 'Completed', 'Canceled'];

export default function AdminOrderDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getAdminOrder(id)
      .then(({ data }) => setOrder(data.data || data))
      .catch(() => { toast.error('Not found'); navigate('/admin/orders'); })
      .finally(() => setLoading(false));
  }, [id, navigate]);

  const handleStatus = async (status) => {
    try {
      await updateOrderStatus(id, status);
      setOrder((p) => ({ ...p, status }));
      toast.success(`Status: ${status}`);
    } catch { toast.error('Failed'); }
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;
  if (!order) return null;

  return (
    <div className="max-w-3xl space-y-6">
      <button onClick={() => navigate('/admin/orders')} className="text-sm text-primary-600 hover:text-primary-700 font-medium">
        ← Back to Orders
      </button>

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <h1 className="text-xl font-bold text-stone-900">Order #{order.id}</h1>
          <Badge variant={order.status}>{order.status}</Badge>
        </div>
        <div className="flex gap-1.5 flex-wrap">
          {statusOptions.map((s) => (
            <Button
              key={s}
              variant={order.status === s ? 'primary' : 'secondary'}
              size="xs"
              onClick={() => handleStatus(s)}
            >
              {s}
            </Button>
          ))}
        </div>
      </div>

      {/* Customer Info */}
      {order.details && (
        <div className="rounded-xl border border-stone-200 bg-white p-5 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div>
            <p className="text-xs font-medium uppercase text-stone-400 mb-1">Customer</p>
            <p className="font-medium text-stone-800">{order.details.first_name} {order.details.last_name}</p>
            <p className="text-stone-600">{order.details.phone}</p>
            <p className="text-stone-600 mt-1">{order.details.address}</p>
          </div>
          <div>
            <p className="text-xs font-medium uppercase text-stone-400 mb-1">Order Info</p>
            <p className="text-stone-600">Date: {formatDateTime(order.created_at || order.order_time)}</p>
            <p className="text-stone-600 capitalize">Payment: {order.details.payment_method === 'cod' ? 'Cash on Delivery' : order.details.payment_method}</p>
            {order.details.change_for && <p className="text-stone-600">Change for: ₱{order.details.change_for}</p>}
            <p className="text-stone-600">Cutlery: {order.details.request_cutlery ? 'Yes' : 'No'}</p>
            {order.details.notes && <p className="text-stone-600 mt-1"><span className="font-medium">Notes:</span> {order.details.notes}</p>}
          </div>
        </div>
      )}

      {/* Items */}
      <div className="rounded-xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        <div className="border-b border-stone-100 px-5 py-3"><h2 className="font-semibold text-stone-900">Items</h2></div>
        <div className="p-5 space-y-3">
          {order.items?.map((item) => (
            <div key={item.id || item.dish_id} className="flex items-center gap-3 rounded-lg border border-stone-100 p-3">
              <img src={getImageUrl(item.image_path)} alt="" className="h-12 w-12 rounded-lg object-cover bg-stone-100" />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-stone-800">{item.dish_name || item.name}</p>
                <p className="text-xs text-stone-500">{item.quantity}x {formatCurrency(item.unit_price || item.price)}</p>
              </div>
              <span className="text-sm font-semibold">{formatCurrency((item.unit_price || item.price) * item.quantity)}</span>
            </div>
          ))}
        </div>
        <div className="border-t border-stone-100 p-5">
          <div className="space-y-1 text-sm max-w-xs ml-auto">
            <div className="flex justify-between text-stone-600"><span>Subtotal</span><span>{formatCurrency(order.subtotal || 0)}</span></div>
            <div className="flex justify-between text-stone-600"><span>Tax</span><span>{formatCurrency(order.tax_amount || 0)}</span></div>
            <div className="flex justify-between text-stone-600"><span>Delivery</span><span>{formatCurrency(order.delivery_fee || 0)}</span></div>
            <div className="flex justify-between font-bold text-stone-900 text-base pt-1 border-t"><span>Total</span><span>{formatCurrency(order.total_amount)}</span></div>
          </div>
        </div>
      </div>
    </div>
  );
}
