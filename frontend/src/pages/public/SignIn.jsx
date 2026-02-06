import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuth } from '../../context/AuthContext';
import Input from '../../components/ui/Input';
import Button from '../../components/ui/Button';
import toast from 'react-hot-toast';

const schema = z.object({
  email: z.string().email('Valid email required'),
  password: z.string().min(1, 'Password is required'),
});

export default function SignIn() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);

  const { register, handleSubmit, formState: { errors } } = useForm({ resolver: zodResolver(schema) });

  const onSubmit = async (data) => {
    setLoading(true);
    try {
      const res = await login(data);
      toast.success('Welcome back!');
      navigate(res.user.role === 'admin' ? '/admin' : '/');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Invalid credentials');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen">
      {/* Left: Form */}
      <div className="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div className="w-full max-w-sm">
          <div className="mb-8 text-center">
            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-lg">TK</div>
            <h1 className="font-heading text-2xl font-bold text-stone-900 sm:text-3xl">Welcome Back</h1>
            <p className="mt-1 text-sm text-stone-500">Where every bite feels like home.</p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <Input label="Email" type="email" placeholder="you@example.com" error={errors.email?.message} {...register('email')} />
            <Input label="Password" type="password" placeholder="••••••••" error={errors.password?.message} {...register('password')} />
            <Button type="submit" loading={loading} className="w-full">Sign In</Button>
          </form>

          <p className="mt-6 text-center text-sm text-stone-500">
            Don't have an account?{' '}
            <Link to="/sign-up" className="font-medium text-primary-600 hover:text-primary-500">Sign up</Link>
          </p>
        </div>
      </div>

      {/* Right: Image */}
      <div className="hidden lg:flex flex-1 items-center justify-center bg-gradient-to-br from-primary-600 to-accent-600 p-12">
        <div className="text-center text-white max-w-md">
          <h2 className="font-heading text-4xl font-bold mb-4">Tonishen's Kitchen</h2>
          <p className="text-lg text-primary-100">Homemade Filipino flavors, cooked fresh daily with love and passion. Order now and taste the warmth of home.</p>
        </div>
      </div>
    </div>
  );
}
