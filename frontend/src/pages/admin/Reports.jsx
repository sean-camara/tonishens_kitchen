import { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { getSalesReport, getTopSelling, exportSalesCsv, exportTopSellingCsv } from '../../api/admin';
import { formatCurrency, formatDate, downloadBlob } from '../../utils/helpers';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import Spinner from '../../components/ui/Spinner';
import toast from 'react-hot-toast';

const periods = [
  { value: 'today', label: 'Today' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
  { value: 'year', label: 'This Year' },
  { value: 'all', label: 'All Time' },
];

export default function Reports() {
  const [tab, setTab] = useState('sales');
  const [period, setPeriod] = useState('month');
  const [salesData, setSalesData] = useState(null);
  const [topData, setTopData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    const fn = tab === 'sales'
      ? getSalesReport({ period }).then(({ data }) => setSalesData(data))
      : getTopSelling({ period }).then(({ data }) => setTopData(data));
    fn.catch(() => toast.error('Failed to load')).finally(() => setLoading(false));
  }, [tab, period]);

  const handleExport = async () => {
    try {
      const fn = tab === 'sales' ? exportSalesCsv({ period }) : exportTopSellingCsv({ period });
      const { data } = await fn;
      downloadBlob(data, `${tab}-report-${period}.csv`);
      toast.success('Exported');
    } catch { toast.error('Export failed'); }
  };

  return (
    <div className="space-y-6">
      <h1 className="font-heading text-2xl font-bold text-stone-900">Reports</h1>

      {/* Tab + Period toggle */}
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex gap-1 rounded-lg bg-stone-100 p-1">
          {[{ key: 'sales', label: 'Sales Report' }, { key: 'top', label: 'Top Selling' }].map((t) => (
            <button
              key={t.key}
              onClick={() => setTab(t.key)}
              className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                tab === t.key ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'
              }`}
            >
              {t.label}
            </button>
          ))}
        </div>
        <div className="flex items-center gap-2">
          <div className="flex gap-1 overflow-x-auto">
            {periods.map((p) => (
              <button
                key={p.value}
                onClick={() => setPeriod(p.value)}
                className={`shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
                  period === p.value ? 'bg-primary-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
                }`}
              >
                {p.label}
              </button>
            ))}
          </div>
          <Button variant="outline" size="sm" onClick={handleExport}>Export CSV</Button>
        </div>
      </div>

      {loading ? (
        <div className="flex justify-center py-20"><Spinner size="lg" /></div>
      ) : tab === 'sales' ? (
        <div className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <Card><Card.Body className="text-center"><p className="text-xs uppercase text-stone-500 mb-1">Total Sales</p><p className="text-2xl font-bold text-stone-900">{formatCurrency(salesData?.total_sales || 0)}</p></Card.Body></Card>
            <Card><Card.Body className="text-center"><p className="text-xs uppercase text-stone-500 mb-1">Orders</p><p className="text-2xl font-bold text-stone-900">{salesData?.total_orders || 0}</p></Card.Body></Card>
            <Card><Card.Body className="text-center"><p className="text-xs uppercase text-stone-500 mb-1">Average Order</p><p className="text-2xl font-bold text-stone-900">{formatCurrency(salesData?.avg_order || 0)}</p></Card.Body></Card>
          </div>

          {salesData?.chart_data?.length > 0 && (
            <Card>
              <Card.Body>
                <div className="h-64 sm:h-80">
                  <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={salesData.chart_data}>
                      <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" />
                      <XAxis dataKey="label" tick={{ fontSize: 12 }} stroke="#78716c" />
                      <YAxis tick={{ fontSize: 12 }} stroke="#78716c" />
                      <Tooltip formatter={(v) => formatCurrency(v)} contentStyle={{ borderRadius: '8px', border: '1px solid #e7e5e4' }} />
                      <Bar dataKey="total" fill="#d97706" radius={[4, 4, 0, 0]} />
                    </BarChart>
                  </ResponsiveContainer>
                </div>
              </Card.Body>
            </Card>
          )}

          {/* Orders Table */}
          {salesData?.orders?.length > 0 && (
            <Card>
              <Card.Header><h2 className="font-semibold text-stone-900">Order Details</h2></Card.Header>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm min-w-[500px]">
                  <thead className="border-b border-stone-200 bg-stone-50">
                    <tr>
                      <th className="px-4 py-3 font-medium text-stone-600">Order</th>
                      <th className="px-4 py-3 font-medium text-stone-600">Date</th>
                      <th className="px-4 py-3 font-medium text-stone-600">Status</th>
                      <th className="px-4 py-3 font-medium text-stone-600 text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-stone-100">
                    {salesData.orders.map((o) => (
                      <tr key={o.id} className="hover:bg-stone-50">
                        <td className="px-4 py-3 font-medium">#{o.id}</td>
                        <td className="px-4 py-3 text-stone-500">{formatDate(o.created_at || o.order_time)}</td>
                        <td className="px-4 py-3 text-stone-600">{o.status}</td>
                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(o.total_amount)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </Card>
          )}
        </div>
      ) : (
        <div className="space-y-6">
          {topData?.chart_data?.length > 0 && (
            <Card>
              <Card.Body>
                <div className="h-64 sm:h-80">
                  <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={topData.chart_data} layout="vertical">
                      <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" />
                      <XAxis type="number" tick={{ fontSize: 12 }} stroke="#78716c" />
                      <YAxis type="category" dataKey="name" width={120} tick={{ fontSize: 12 }} stroke="#78716c" />
                      <Tooltip contentStyle={{ borderRadius: '8px', border: '1px solid #e7e5e4' }} />
                      <Bar dataKey="total_sold" fill="#f97316" radius={[0, 4, 4, 0]} />
                    </BarChart>
                  </ResponsiveContainer>
                </div>
              </Card.Body>
            </Card>
          )}

          {topData?.dishes?.length > 0 && (
            <Card>
              <Card.Header><h2 className="font-semibold text-stone-900">Top Selling Dishes</h2></Card.Header>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm">
                  <thead className="border-b border-stone-200 bg-stone-50">
                    <tr>
                      <th className="px-4 py-3 font-medium text-stone-600">#</th>
                      <th className="px-4 py-3 font-medium text-stone-600">Dish</th>
                      <th className="px-4 py-3 font-medium text-stone-600 text-right">Sold</th>
                      <th className="px-4 py-3 font-medium text-stone-600 text-right">Revenue</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-stone-100">
                    {topData.dishes.map((d, i) => (
                      <tr key={d.id || d.name} className="hover:bg-stone-50">
                        <td className="px-4 py-3 text-stone-500">{i + 1}</td>
                        <td className="px-4 py-3 font-medium text-stone-800">{d.name}</td>
                        <td className="px-4 py-3 text-right">{d.total_sold}</td>
                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(d.revenue || 0)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </Card>
          )}
        </div>
      )}
    </div>
  );
}
