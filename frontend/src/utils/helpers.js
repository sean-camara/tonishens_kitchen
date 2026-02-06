export const formatCurrency = (amount) =>
  `₱${Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

export const formatDate = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
};

export const formatDateTime = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true,
  });
};

export const statusColors = {
  Pending: 'bg-yellow-100 text-yellow-800',
  Preparing: 'bg-blue-100 text-blue-800',
  'On the Way': 'bg-purple-100 text-purple-800',
  Completed: 'bg-emerald-100 text-emerald-800',
  Canceled: 'bg-red-100 text-red-800',
};

export const getImageUrl = (path) => {
  if (!path) return '/placeholder-dish.svg';
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

export const downloadBlob = (blob, filename) => {
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  window.URL.revokeObjectURL(url);
};
