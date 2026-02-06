import { useState, useEffect, useCallback } from 'react';
import { getAdminOrders, updateOrderStatus, bulkUpdateOrders } from '../../api/admin';
import { formatCurrency, formatDateTime, statusColors } from '../../utils/helpers';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Select from '../../components/ui/Select';
import Spinner from '../../components/ui/Spinner';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';

const statuses = ['All', 'Pending', 'Preparing', 'On the Way', 'Completed', 'Canceled'];
const statusOptions = [
  { value: 'Pending', label: 'Pending' },
  { value: 'Preparing', label: 'Preparing' },
  { value: 'On the Way', label: 'On the Way' },
  { value: 'Completed', label: 'Completed' },
  { value: 'Canceled', label: 'Canceled' },
];

export default function AdminOrders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('All');
  const [selected, setSelected] = useState([]);
  const [bulkStatus, setBulkStatus] = useState('');

  const load = useCallback(async () => {
    try {
      const params = filter !== 'All' ? { status: filter } : {};
      const { data } = await getAdminOrders(params);
      setOrders(data.data || data);
    } catch { toast.error('Failed to load orders'); }
    finally { setLoading(false); }
  }, [filter]);

  useEffect(() => { load(); }, [load]);

  const handleStatusChange = async (orderId, newStatus) => {
    try {
      await updateOrderStatus(orderId, newStatus);
      setOrders((prev) => prev.map((o) => o.id === orderId ? { ...o, status: newStatus } : o));
      toast.success('Status updated');
    } catch { toast.error('Failed to update'); }
  };

  const handleBulkUpdate = async () => {
    if (!bulkStatus || selected.length === 0) return;
    try {
      await bulkUpdateOrders(selected, bulkStatus);
      load();
      setSelected([]);
      setBulkStatus('');
      toast.success(`Updated ${selected.length} orders`);
    } catch { toast.error('Bulk update failed'); }
  };

  const toggleSelect = (id) => setSelected((p) => p.includes(id) ? p.filter((x) => x !== id) : [...p, id]);
  const toggleAll = () => setSelected(selected.length === orders.length ? [] : orders.map((o) => o.id));

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="space-y-6">
      <h1 className="font-heading text-2xl font-bold text-stone-900">Orders</h1>

      {/* Filters */}
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex gap-1.5 overflow-x-auto pb-1">
          {statuses.map((s) => (
            <button
              key={s}
              onClick={() => { setFilter(s); setLoading(true); }}
              className={`shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
                filter === s ? 'bg-primary-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
              }`}
            >
              {s}
            </button>
          ))}
        </div>
        {selected.length > 0 && (
          <div className="flex items-center gap-2">
            <span className="text-sm text-stone-600">{selected.length} selected</span>
            <Select value={bulkStatus} onChange={(e) => setBulkStatus(e.target.value)} placeholder="Set status" options={statusOptions} className="w-36" />
            <Button size="sm" onClick={handleBulkUpdate} disabled={!bulkStatus}>Apply</Button>
          </div>
        )}
      </div>

      {/* Desktop Table */}
      <div className="hidden md:block overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-stone-200 bg-stone-50">
            <tr>
              <th className="px-4 py-3 w-8">
                <input type="checkbox" className="rounded border-stone-300" checked={selected.length === orders.length && orders.length > 0} onChange={toggleAll} />
              </th>
              <th className="px-4 py-3 font-medium text-stone-600">Order</th>
              <th className="px-4 py-3 font-medium text-stone-600">Customer</th>
              <th className="px-4 py-3 font-medium text-stone-600">Date</th>
              <th className="px-4 py-3 font-medium text-stone-600">Total</th>
              <th className="px-4 py-3 font-medium text-stone-600">Status</th>
              <th className="px-4 py-3 font-medium text-stone-600 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-stone-100">
            {orders.map((order) => (
              <tr key={order.id} className="hover:bg-stone-50 transition-colors">
                <td className="px-4 py-3">
                  <input type="checkbox" className="rounded border-stone-300" checked={selected.includes(order.id)} onChange={() => toggleSelect(order.id)} />
                </td>
                <td className="px-4 py-3 font-medium text-stone-800">#{order.id}</td>
                <td className="px-4 py-3 text-stone-600">{order.user?.first_name} {order.user?.last_name}</td>
                <td className="px-4 py-3 text-stone-500 text-xs">{formatDateTime(order.created_at || order.order_time)}</td>
                <td className="px-4 py-3 font-medium text-stone-800">{formatCurrency(order.total_amount)}</td>
                <td className="px-4 py-3">
                  <select
                    value={order.status}
                    onChange={(e) => handleStatusChange(order.id, e.target.value)}
                    className={`rounded-full px-2 py-1 text-xs font-medium border-0 cursor-pointer ${statusColors[order.status] || ''}`}
                  >
                    {statusOptions.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                  </select>
                </td>
                <td className="px-4 py-3 text-right">
                  <Link to={`/admin/orders/${order.id}`} className="text-xs font-medium text-primary-600 hover:text-primary-700">View</Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Mobile cards */}
      <div className="md:hidden space-y-3">
        {orders.map((order) => (
          <Link key={order.id} to={`/admin/orders/${order.id}`} className="block rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between mb-2">
              <span className="font-semibold text-stone-900">#{order.id}</span>
              <Badge variant={order.status}>{order.status}</Badge>
            </div>
            <p className="text-sm text-stone-600">{order.user?.first_name} {order.user?.last_name}</p>
            <div className="flex items-center justify-between mt-2">
              <span className="text-xs text-stone-500">{formatDateTime(order.created_at || order.order_time)}</span>
              <span className="font-semibold text-stone-800">{formatCurrency(order.total_amount)}</span>
            </div>
          </Link>
        ))}
      </div>

      {orders.length === 0 && <p className="text-center text-stone-500 py-8">No orders found</p>}
    </div>
  );
}
