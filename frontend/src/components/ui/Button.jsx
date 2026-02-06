import { forwardRef } from 'react';
import Spinner from './Spinner';

const variants = {
  primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
  secondary: 'bg-stone-200 text-stone-800 hover:bg-stone-300 focus:ring-stone-400',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
  ghost: 'bg-transparent text-stone-600 hover:bg-stone-100 focus:ring-stone-400',
  outline: 'border border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
};

const sizes = {
  xs: 'px-2.5 py-1 text-xs',
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2 text-sm',
  lg: 'px-5 py-2.5 text-base',
  xl: 'px-6 py-3 text-base',
};

const Button = forwardRef(({ variant = 'primary', size = 'md', loading = false, disabled = false, children, className = '', ...props }, ref) => (
  <button
    ref={ref}
    disabled={disabled || loading}
    className={`inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-all duration-200
      focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50
      ${variants[variant]} ${sizes[size]} ${className}`}
    {...props}
  >
    {loading && <Spinner size="sm" className="text-current" />}
    {children}
  </button>
));

Button.displayName = 'Button';
export default Button;
