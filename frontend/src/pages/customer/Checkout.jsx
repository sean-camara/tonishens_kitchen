import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useCart } from '../../context/CartContext';
import { placeOrder } from '../../api/orders';
import { getProfile } from '../../api/profile';
import { formatCurrency, getImageUrl } from '../../utils/helpers';
import Input from '../../components/ui/Input';
import Textarea from '../../components/ui/Textarea';
import Button from '../../components/ui/Button';
import toast from 'react-hot-toast';

const schema = z.object({
  first_name: z.string().min(1, 'Required'),
  last_name: z.string().min(1, 'Required'),
  phone: z.string().min(10, 'Valid phone required'),
  address: z.string().min(5, 'Address required'),
  notes: z.string().optional(),
  request_cutlery: z.boolean().optional(),
  payment_method: z.enum(['cod', 'gcash', 'card'], { required_error: 'Select payment method' }),
  change_for: z.string().optional(),
});

export default function Checkout() {
  const { items, subtotal, tax, deliveryFee, grandTotal, fetchCart } = useCart();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [step, setStep] = useState(1);

  const { register, handleSubmit, formState: { errors }, setValue, watch } = useForm({
    resolver: zodResolver(schema),
    defaultValues: { request_cutlery: false, payment_method: 'cod' },
  });

  const paymentMethod = watch('payment_method');

  useEffect(() => {
    if (items.length === 0) { navigate('/cart'); return; }
    getProfile().then(({ data }) => {
      if (data.user) {
        setValue('first_name', data.user.first_name || '');
        setValue('last_name', data.user.last_name || '');
        setValue('phone', data.user.phone || '');
        setValue('address', data.user.address || '');
      }
    }).catch(() => {});
  }, [items.length, navigate, setValue]);

  const onSubmit = async (formData) => {
    setLoading(true);
    try {
      await placeOrder(formData);
      await fetchCart();
      toast.success('Order placed successfully!');
      navigate('/orders');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to place order');
    } finally {
      setLoading(false);
    }
  };

  const steps = [
    { num: 1, label: 'Delivery' },
    { num: 2, label: 'Payment' },
    { num: 3, label: 'Review' },
  ];

  return (
    <div className="container-app py-8 sm:py-12 max-w-4xl">
      <h1 className="font-heading text-2xl font-bold text-stone-900 sm:text-3xl mb-6">Checkout</h1>

      {/* Stepper */}
      <div className="mb-8 flex items-center justify-center gap-2">
        {steps.map((s, i) => (
          <div key={s.num} className="flex items-center gap-2">
            <button
              onClick={() => s.num < step && setStep(s.num)}
              className={`flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-colors ${
                step >= s.num ? 'bg-primary-600 text-white' : 'bg-stone-200 text-stone-500'
              }`}
            >
              {s.num}
            </button>
            <span className={`hidden sm:inline text-sm font-medium ${step >= s.num ? 'text-primary-700' : 'text-stone-400'}`}>{s.label}</span>
            {i < steps.length - 1 && <div className={`h-px w-8 sm:w-16 ${step > s.num ? 'bg-primary-600' : 'bg-stone-200'}`} />}
          </div>
        ))}
      </div>

      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-5">
          {/* Form area */}
          <div className="lg:col-span-3">
            {step === 1 && (
              <div className="rounded-xl border border-stone-200 bg-white p-5 shadow-sm space-y-4">
                <h2 className="text-lg font-semibold text-stone-900">Delivery Details</h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <Input label="First Name" error={errors.first_name?.message} {...register('first_name')} />
                  <Input label="Last Name" error={errors.last_name?.message} {...register('last_name')} />
                </div>
                <Input label="Phone" type="tel" error={errors.phone?.message} {...register('phone')} />
                <Textarea label="Delivery Address" error={errors.address?.message} {...register('address')} />
                <Textarea label="Notes (optional)" placeholder="e.g. No ketchup please" {...register('notes')} />
                <label className="flex items-center gap-2 text-sm text-stone-600">
                  <input type="checkbox" className="rounded border-stone-300 text-primary-600 focus:ring-primary-500" {...register('request_cutlery')} />
                  Include cutlery
                </label>
                <Button type="button" onClick={() => setStep(2)} className="w-full sm:w-auto">Next: Payment</Button>
              </div>
            )}

            {step === 2 && (
              <div className="rounded-xl border border-stone-200 bg-white p-5 shadow-sm space-y-4">
                <h2 className="text-lg font-semibold text-stone-900">Payment Method</h2>
                {['cod', 'gcash', 'card'].map((method) => (
                  <label key={method} className={`flex items-center gap-3 rounded-lg border p-4 cursor-pointer transition-colors ${
                    paymentMethod === method ? 'border-primary-500 bg-primary-50' : 'border-stone-200 hover:border-stone-300'
                  } ${method !== 'cod' ? 'opacity-50 cursor-not-allowed' : ''}`}>
                    <input type="radio" value={method} disabled={method !== 'cod'} className="text-primary-600 focus:ring-primary-500" {...register('payment_method')} />
                    <div>
                      <p className="font-medium text-stone-800">
                        {method === 'cod' ? 'Cash on Delivery' : method === 'gcash' ? 'GCash' : 'Debit/Credit Card'}
                      </p>
                      {method !== 'cod' && <p className="text-xs text-stone-400">Coming soon</p>}
                    </div>
                  </label>
                ))}
                {paymentMethod === 'cod' && (
                  <Input label="Change for (optional)" placeholder="e.g. 1000" {...register('change_for')} />
                )}
                {errors.payment_method && <p className="text-xs text-red-500">{errors.payment_method.message}</p>}
                <div className="flex gap-3">
                  <Button type="button" variant="secondary" onClick={() => setStep(1)}>Back</Button>
                  <Button type="button" onClick={() => setStep(3)}>Next: Review</Button>
                </div>
              </div>
            )}

            {step === 3 && (
              <div className="rounded-xl border border-stone-200 bg-white p-5 shadow-sm space-y-4">
                <h2 className="text-lg font-semibold text-stone-900">Review Order</h2>
                <div className="space-y-2">
                  {items.map((item) => (
                    <div key={item.dish_id} className="flex items-center gap-3 rounded-lg border border-stone-100 p-3">
                      <img src={getImageUrl(item.image_path)} alt="" className="h-12 w-12 rounded-lg object-cover bg-stone-100" />
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-stone-800 truncate">{item.name}</p>
                        <p className="text-xs text-stone-500">{item.quantity}x {formatCurrency(item.price)}</p>
                      </div>
                      <span className="text-sm font-semibold text-stone-800">{formatCurrency(item.price * item.quantity)}</span>
                    </div>
                  ))}
                </div>
                <div className="flex gap-3">
                  <Button type="button" variant="secondary" onClick={() => setStep(2)}>Back</Button>
                  <Button type="submit" loading={loading} className="flex-1">Place Order — {formatCurrency(grandTotal)}</Button>
                </div>
              </div>
            )}
          </div>

          {/* Sidebar summary */}
          <div className="lg:col-span-2">
            <div className="sticky top-20 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
              <h3 className="text-lg font-semibold text-stone-900 mb-4">Summary</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between text-stone-600"><span>Items ({items.length})</span><span>{formatCurrency(subtotal)}</span></div>
                <div className="flex justify-between text-stone-600"><span>Tax (12%)</span><span>{formatCurrency(tax)}</span></div>
                <div className="flex justify-between text-stone-600"><span>Delivery</span><span>{formatCurrency(deliveryFee)}</span></div>
                <div className="border-t border-stone-200 pt-2 flex justify-between font-semibold text-stone-900 text-base">
                  <span>Total</span><span>{formatCurrency(grandTotal)}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}
