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
  first_name: z.string().min(1, 'First name is required').max(100),
  last_name: z.string().min(1, 'Last name is required').max(100),
  email: z.string().email('Valid email required'),
  password: z.string().min(8, 'At least 8 characters').regex(/[A-Z]/, 'Include an uppercase letter').regex(/[0-9]/, 'Include a number'),
  password_confirmation: z.string(),
}).refine((d) => d.password === d.password_confirmation, { message: 'Passwords must match', path: ['password_confirmation'] });

export default function SignUp() {
  const { register: authRegister } = useAuth();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);

  const { register, handleSubmit, formState: { errors }, watch } = useForm({ resolver: zodResolver(schema) });
  const password = watch('password', '');
  const strength = [/.{8,}/, /[A-Z]/, /[0-9]/, /[^A-Za-z0-9]/].filter((r) => r.test(password)).length;

  const onSubmit = async (data) => {
    setLoading(true);
    try {
      await authRegister(data);
      toast.success('Account created!');
      navigate('/');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Registration failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen">
      {/* Left: Image */}
      <div className="hidden lg:flex flex-1 items-center justify-center bg-gradient-to-br from-accent-600 to-primary-600 p-12">
        <div className="text-center text-white max-w-md">
          <h2 className="font-heading text-4xl font-bold mb-4">Join Our Family</h2>
          <p className="text-lg text-accent-100">Create an account to order fresh, homemade Filipino dishes and get them delivered to your door.</p>
        </div>
      </div>

      {/* Right: Form */}
      <div className="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div className="w-full max-w-sm">
          <div className="mb-8 text-center">
            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-lg">TK</div>
            <h1 className="font-heading text-2xl font-bold text-stone-900 sm:text-3xl">Let's Get Started</h1>
            <p className="mt-1 text-sm text-stone-500">Create your account in seconds.</p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <div className="grid grid-cols-2 gap-3">
              <Input label="First Name" placeholder="Juan" error={errors.first_name?.message} {...register('first_name')} />
              <Input label="Last Name" placeholder="Dela Cruz" error={errors.last_name?.message} {...register('last_name')} />
            </div>
            <Input label="Email" type="email" placeholder="you@example.com" error={errors.email?.message} {...register('email')} />
            <div>
              <Input label="Password" type="password" placeholder="••••••••" error={errors.password?.message} {...register('password')} />
              {password && (
                <div className="mt-2 flex gap-1">
                  {[1, 2, 3, 4].map((i) => (
                    <div key={i} className={`h-1 flex-1 rounded-full transition-colors ${
                      i <= strength ? (strength <= 2 ? 'bg-red-400' : strength === 3 ? 'bg-yellow-400' : 'bg-emerald-400') : 'bg-stone-200'
                    }`} />
                  ))}
                </div>
              )}
            </div>
            <Input label="Confirm Password" type="password" placeholder="••••••••" error={errors.password_confirmation?.message} {...register('password_confirmation')} />
            <Button type="submit" loading={loading} className="w-full">Create Account</Button>
          </form>

          <p className="mt-6 text-center text-sm text-stone-500">
            Already have an account?{' '}
            <Link to="/sign-in" className="font-medium text-primary-600 hover:text-primary-500">Sign in</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
