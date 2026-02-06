import { forwardRef } from 'react';

const Textarea = forwardRef(({ label, error, className = '', ...props }, ref) => (
  <div className="w-full">
    {label && <label className="mb-1 block text-sm font-medium text-stone-700">{label}</label>}
    <textarea
      ref={ref}
      rows={3}
      className={`w-full rounded-lg border px-3 py-2 text-sm text-stone-800 shadow-sm transition-colors
        placeholder:text-stone-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 resize-none
        ${error ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : 'border-stone-300'}
        ${className}`}
      {...props}
    />
    {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
  </div>
));

Textarea.displayName = 'Textarea';
export default Textarea;
