export default function Card({ children, className = '', hover = false, ...props }) {
  return (
    <div
      className={`rounded-xl border border-stone-200 bg-white shadow-sm
        ${hover ? 'transition-all duration-300 hover:shadow-md hover:-translate-y-0.5' : ''}
        ${className}`}
      {...props}
    >
      {children}
    </div>
  );
}

Card.Header = ({ children, className = '' }) => (
  <div className={`border-b border-stone-100 px-5 py-4 ${className}`}>{children}</div>
);

Card.Body = ({ children, className = '' }) => (
  <div className={`px-5 py-4 ${className}`}>{children}</div>
);

Card.Footer = ({ children, className = '' }) => (
  <div className={`border-t border-stone-100 px-5 py-3 ${className}`}>{children}</div>
);
