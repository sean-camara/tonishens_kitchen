import { forwardRef } from 'react';

const Select = forwardRef(({ label, error, options = [], placeholder, className = '', ...props }, ref) => (
  <div className="w-full">
    {label && <label className="mb-1 block text-sm font-medium text-stone-700">{label}</label>}
    <select
      ref={ref}
      className={`w-full rounded-lg border px-3 py-2 text-sm text-stone-800 shadow-sm transition-colors
        focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 bg-white
        ${error ? 'border-red-400' : 'border-stone-300'}
        ${className}`}
      {...props}
    >
      {placeholder && <option value="">{placeholder}</option>}
      {options.map((opt) => (
        <option key={opt.value} value={opt.value}>{opt.label}</option>
      ))}
    </select>
    {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
  </div>
));

Select.displayName = 'Select';
export default Select;
