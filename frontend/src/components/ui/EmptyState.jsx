export default function EmptyState({ icon: Icon, title, description, action }) {
  return (
    <div className="flex flex-col items-center justify-center py-12 text-center">
      {Icon && <Icon className="mx-auto h-12 w-12 text-stone-300" />}
      <h3 className="mt-3 text-sm font-semibold text-stone-800">{title}</h3>
      {description && <p className="mt-1 text-sm text-stone-500">{description}</p>}
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}
