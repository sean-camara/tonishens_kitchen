import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getOrder, cancelOrder, submitFeedback } from '../../api/orders';
import { formatCurrency, formatDateTime, getImageUrl } from '../../utils/helpers';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import StarRating from '../../components/ui/StarRating';
import Modal from '../../components/ui/Modal';
import Textarea from '../../components/ui/Textarea';
import toast from 'react-hot-toast';

const statusSteps = ['Pending', 'Preparing', 'On the Way', 'Completed'];

export default function OrderDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [canceling, setCanceling] = useState(false);
  const [feedbackModal, setFeedbackModal] = useState(false);
  const [ratings, setRatings] = useState({});
  const [comment, setComment] = useState('');
  const [submittingFeedback, setSubmittingFeedback] = useState(false);

  useEffect(() => {
    getOrder(id)
      .then(({ data }) => setOrder(data.data || data))
      .catch(() => { toast.error('Order not found'); navigate('/orders'); })
      .finally(() => setLoading(false));
  }, [id, navigate]);

  const handleCancel = async () => {
    if (!confirm('Cancel this order?')) return;
    setCanceling(true);
    try {
      await cancelOrder(id);
      setOrder((prev) => ({ ...prev, status: 'Canceled' }));
      toast.success('Order canceled');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Cannot cancel');
    } finally {
      setCanceling(false);
    }
  };

  const handleFeedback = async () => {
    setSubmittingFeedback(true);
    try {
      const feedbacks = Object.entries(ratings).map(([dish_id, rating]) => ({ dish_id: Number(dish_id), rating, comment }));
      await submitFeedback(id, { feedbacks });
      toast.success('Thank you for your feedback!');
      setFeedbackModal(false);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to submit feedback');
    } finally {
      setSubmittingFeedback(false);
    }
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;
  if (!order) return null;

  const currentStep = order.status === 'Canceled' ? -1 : statusSteps.indexOf(order.status);

  return (
    <div className="container-app py-8 sm:py-12 max-w-3xl">
      <button onClick={() => navigate('/orders')} className="mb-4 text-sm text-primary-600 hover:text-primary-700 font-medium">
        ← Back to Orders
      </button>

      <div className="rounded-xl border border-stone-200 bg-white shadow-sm overflow-hidden">
        {/* Header */}
        <div className="border-b border-stone-100 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-xl font-bold text-stone-900">Order #{order.id}</h1>
              <Badge variant={order.status}>{order.status}</Badge>
            </div>
            <p className="text-sm text-stone-500 mt-0.5">{formatDateTime(order.created_at || order.order_time)}</p>
          </div>
          <div className="flex gap-2">
            {order.status === 'Pending' && (
              <Button variant="danger" size="sm" loading={canceling} onClick={handleCancel}>Cancel Order</Button>
            )}
            {order.status === 'Completed' && (
              <Button variant="outline" size="sm" onClick={() => setFeedbackModal(true)}>Leave Feedback</Button>
            )}
          </div>
        </div>

        {/* Status Timeline */}
        {order.status !== 'Canceled' && (
          <div className="border-b border-stone-100 p-5">
            <div className="flex items-center justify-between">
              {statusSteps.map((s, i) => (
                <div key={s} className="flex flex-1 items-center">
                  <div className="flex flex-col items-center">
                    <div className={`flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-colors ${
                      i <= currentStep ? 'bg-primary-600 text-white' : 'bg-stone-200 text-stone-400'
                    }`}>
                      {i + 1}
                    </div>
                    <span className={`mt-1 text-[10px] sm:text-xs font-medium ${i <= currentStep ? 'text-primary-700' : 'text-stone-400'}`}>
                      {s}
                    </span>
                  </div>
                  {i < statusSteps.length - 1 && (
                    <div className={`mx-1 h-0.5 flex-1 ${i < currentStep ? 'bg-primary-600' : 'bg-stone-200'}`} />
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Items */}
        <div className="p-5 space-y-3">
          <h3 className="font-semibold text-stone-900">Items</h3>
          {order.items?.map((item) => (
            <div key={item.id || item.dish_id} className="flex items-center gap-3 rounded-lg border border-stone-100 p-3">
              <img src={getImageUrl(item.image_path)} alt="" className="h-12 w-12 rounded-lg object-cover bg-stone-100" />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-stone-800 truncate">{item.dish_name || item.name}</p>
                <p className="text-xs text-stone-500">{item.quantity}x {formatCurrency(item.unit_price || item.price)}</p>
              </div>
              <span className="text-sm font-semibold text-stone-800">{formatCurrency((item.unit_price || item.price) * item.quantity)}</span>
            </div>
          ))}
        </div>

        {/* Delivery Details */}
        {order.details && (
          <div className="border-t border-stone-100 p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
              <p className="text-xs font-medium text-stone-400 uppercase mb-1">Deliver To</p>
              <p className="text-stone-800">{order.details.first_name} {order.details.last_name}</p>
              <p className="text-stone-600">{order.details.phone}</p>
              <p className="text-stone-600">{order.details.address}</p>
            </div>
            <div>
              <p className="text-xs font-medium text-stone-400 uppercase mb-1">Payment</p>
              <p className="text-stone-800 capitalize">{order.details.payment_method === 'cod' ? 'Cash on Delivery' : order.details.payment_method}</p>
              {order.details.notes && <p className="mt-2 text-stone-600"><span className="font-medium">Notes:</span> {order.details.notes}</p>}
            </div>
          </div>
        )}

        {/* Totals */}
        <div className="border-t border-stone-100 p-5">
          <div className="space-y-1 text-sm max-w-xs ml-auto">
            <div className="flex justify-between text-stone-600"><span>Subtotal</span><span>{formatCurrency(order.subtotal || 0)}</span></div>
            <div className="flex justify-between text-stone-600"><span>Tax</span><span>{formatCurrency(order.tax_amount || 0)}</span></div>
            <div className="flex justify-between text-stone-600"><span>Delivery</span><span>{formatCurrency(order.delivery_fee || 0)}</span></div>
            <div className="flex justify-between font-bold text-stone-900 text-base pt-1 border-t border-stone-200">
              <span>Total</span><span>{formatCurrency(order.total_amount)}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Feedback Modal */}
      <Modal open={feedbackModal} onClose={() => setFeedbackModal(false)} title="Rate Your Order">
        <div className="space-y-4">
          {order.items?.map((item) => (
            <div key={item.dish_id} className="flex items-center justify-between gap-3">
              <span className="text-sm font-medium text-stone-700">{item.dish_name || item.name}</span>
              <StarRating rating={ratings[item.dish_id] || 0} onRate={(r) => setRatings((p) => ({ ...p, [item.dish_id]: r }))} />
            </div>
          ))}
          <Textarea label="Comment (optional)" value={comment} onChange={(e) => setComment(e.target.value)} placeholder="Tell us about your experience..." />
          <Button onClick={handleFeedback} loading={submittingFeedback} className="w-full">Submit Feedback</Button>
        </div>
      </Modal>
    </div>
  );
}
