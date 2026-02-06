import { useState, useEffect } from 'react';
import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { getDishes, getCategories } from '../../api/dishes';
import { useCart } from '../../context/CartContext';
import { useAuth } from '../../context/AuthContext';
import { formatCurrency, getImageUrl } from '../../utils/helpers';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import toast from 'react-hot-toast';
import { useNavigate } from 'react-router-dom';

export default function MenuPage() {
  const [dishes, setDishes] = useState([]);
  const [categories, setCategories] = useState([]);
  const [activeCategory, setActiveCategory] = useState('all');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const { addToCart } = useCart();
  const { isAuthenticated } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    const load = async () => {
      try {
        const [dishRes, catRes] = await Promise.all([getDishes(), getCategories()]);
        setDishes(dishRes.data.data || dishRes.data);
        setCategories(catRes.data.data || catRes.data);
      } catch { toast.error('Failed to load menu'); }
      finally { setLoading(false); }
    };
    load();
  }, []);

  const filtered = dishes.filter((d) => {
    const matchCat = activeCategory === 'all' || d.category?.id === activeCategory || d.category_id === activeCategory;
    const matchSearch = d.name?.toLowerCase().includes(search.toLowerCase());
    return matchCat && matchSearch;
  });

  const handleAdd = (dishId) => {
    if (!isAuthenticated) { navigate('/sign-in'); return; }
    addToCart(dishId);
  };

  return (
    <div className="container-app py-8 sm:py-12">
      <div className="text-center mb-8">
        <h1 className="font-heading text-3xl font-bold text-stone-900 sm:text-4xl">Our Menu</h1>
        <p className="mt-2 text-stone-500">Discover our homemade Filipino dishes</p>
      </div>

      {/* Search & Filter */}
      <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="relative flex-1 max-w-md">
          <MagnifyingGlassIcon className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
          <input
            type="text"
            placeholder="Search dishes..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full rounded-lg border border-stone-300 py-2 pl-9 pr-3 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
          />
        </div>

        <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
          <button
            onClick={() => setActiveCategory('all')}
            className={`shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition-colors ${
              activeCategory === 'all' ? 'bg-primary-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
            }`}
          >
            All
          </button>
          {categories.map((c) => (
            <button
              key={c.id}
              onClick={() => setActiveCategory(c.id)}
              className={`shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition-colors ${
                activeCategory === c.id ? 'bg-primary-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
              }`}
            >
              {c.name}
            </button>
          ))}
        </div>
      </div>

      {/* Dishes Grid */}
      {loading ? (
        <div className="flex justify-center py-20"><Spinner size="lg" /></div>
      ) : filtered.length === 0 ? (
        <div className="py-20 text-center text-stone-500">No dishes found.</div>
      ) : (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {filtered.map((dish) => (
            <Card key={dish.id} hover className="overflow-hidden flex flex-col">
              <div className="aspect-[4/3] overflow-hidden bg-stone-100">
                <img
                  src={getImageUrl(dish.image_path)}
                  alt={dish.name}
                  className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                  loading="lazy"
                />
              </div>
              <Card.Body className="flex flex-1 flex-col">
                <div className="mb-1 flex items-start justify-between gap-2">
                  <h3 className="font-semibold text-stone-900 line-clamp-1">{dish.name}</h3>
                  <span className="shrink-0 font-bold text-primary-600">{formatCurrency(dish.price)}</span>
                </div>
                {dish.category && (
                  <span className="mb-2 inline-block w-fit rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700">
                    {dish.category.name || dish.category}
                  </span>
                )}
                <p className="flex-1 text-sm text-stone-500 line-clamp-2 mb-3">{dish.description}</p>
                <Button size="sm" className="w-full" onClick={() => handleAdd(dish.id)}>
                  Add to Cart
                </Button>
              </Card.Body>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}
