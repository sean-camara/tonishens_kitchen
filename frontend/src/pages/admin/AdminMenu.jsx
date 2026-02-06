import { useState, useEffect, useCallback } from 'react';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { getAdminDishes, createDish, updateDish, deleteDish, getAdminCategories, createCategory } from '../../api/admin';
import { formatCurrency, getImageUrl } from '../../utils/helpers';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Textarea from '../../components/ui/Textarea';
import Select from '../../components/ui/Select';
import Modal from '../../components/ui/Modal';
import Spinner from '../../components/ui/Spinner';
import toast from 'react-hot-toast';

export default function AdminMenu() {
  const [dishes, setDishes] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ name: '', description: '', category_id: '', price: '', image: null });
  const [imagePreview, setImagePreview] = useState(null);

  const load = useCallback(async () => {
    try {
      const [dRes, cRes] = await Promise.all([getAdminDishes(), getAdminCategories()]);
      setDishes(dRes.data.data || dRes.data);
      setCategories(cRes.data.data || cRes.data);
    } catch { toast.error('Failed to load'); }
    finally { setLoading(false); }
  }, []);

  useEffect(() => { load(); }, [load]);

  const openAdd = () => {
    setEditing(null);
    setForm({ name: '', description: '', category_id: '', price: '', image: null });
    setImagePreview(null);
    setModalOpen(true);
  };

  const openEdit = (dish) => {
    setEditing(dish);
    setForm({ name: dish.name, description: dish.description || '', category_id: dish.category_id || '', price: dish.price, image: null });
    setImagePreview(getImageUrl(dish.image_path));
    setModalOpen(true);
  };

  const handleDelete = async (id) => {
    if (!confirm('Delete this dish?')) return;
    try { await deleteDish(id); load(); toast.success('Deleted'); } catch { toast.error('Failed'); }
  };

  const handleSave = async () => {
    setSaving(true);
    const fd = new FormData();
    fd.append('name', form.name);
    fd.append('description', form.description);
    fd.append('category_id', form.category_id);
    fd.append('price', form.price);
    if (form.image) fd.append('image', form.image);
    if (editing) fd.append('_method', 'PUT');

    try {
      editing ? await updateDish(editing.id, fd) : await createDish(fd);
      toast.success(editing ? 'Updated' : 'Created');
      setModalOpen(false);
      load();
    } catch { toast.error('Failed to save'); }
    finally { setSaving(false); }
  };

  const handleImagePick = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setForm((p) => ({ ...p, image: file }));
    setImagePreview(URL.createObjectURL(file));
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="font-heading text-2xl font-bold text-stone-900">Menu Management</h1>
        <Button onClick={openAdd} size="sm"><PlusIcon className="h-4 w-4" /> Add Dish</Button>
      </div>

      {/* Table for desktop, cards for mobile */}
      <div className="hidden sm:block overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-stone-200 bg-stone-50">
            <tr>
              <th className="px-4 py-3 font-medium text-stone-600">Dish</th>
              <th className="px-4 py-3 font-medium text-stone-600">Category</th>
              <th className="px-4 py-3 font-medium text-stone-600">Price</th>
              <th className="px-4 py-3 font-medium text-stone-600 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-stone-100">
            {dishes.map((dish) => (
              <tr key={dish.id} className="hover:bg-stone-50 transition-colors">
                <td className="px-4 py-3">
                  <div className="flex items-center gap-3">
                    <img src={getImageUrl(dish.image_path)} alt="" className="h-10 w-10 rounded-lg object-cover bg-stone-100" />
                    <div>
                      <p className="font-medium text-stone-800">{dish.name}</p>
                      <p className="text-xs text-stone-500 truncate max-w-[200px]">{dish.description}</p>
                    </div>
                  </div>
                </td>
                <td className="px-4 py-3 text-stone-600">{dish.category?.name || '—'}</td>
                <td className="px-4 py-3 font-medium text-stone-800">{formatCurrency(dish.price)}</td>
                <td className="px-4 py-3 text-right">
                  <div className="flex justify-end gap-1">
                    <button onClick={() => openEdit(dish)} className="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600"><PencilIcon className="h-4 w-4" /></button>
                    <button onClick={() => handleDelete(dish.id)} className="rounded-lg p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-500"><TrashIcon className="h-4 w-4" /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Mobile cards */}
      <div className="sm:hidden space-y-3">
        {dishes.map((dish) => (
          <div key={dish.id} className="flex gap-3 rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
            <img src={getImageUrl(dish.image_path)} alt="" className="h-16 w-16 rounded-lg object-cover bg-stone-100 shrink-0" />
            <div className="flex-1 min-w-0">
              <p className="font-medium text-stone-800 truncate">{dish.name}</p>
              <p className="text-xs text-stone-500">{dish.category?.name}</p>
              <p className="text-sm font-semibold text-primary-600">{formatCurrency(dish.price)}</p>
            </div>
            <div className="flex flex-col gap-1 shrink-0">
              <button onClick={() => openEdit(dish)} className="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100"><PencilIcon className="h-4 w-4" /></button>
              <button onClick={() => handleDelete(dish.id)} className="rounded-lg p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-500"><TrashIcon className="h-4 w-4" /></button>
            </div>
          </div>
        ))}
      </div>

      {/* Add/Edit Modal */}
      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Edit Dish' : 'Add Dish'}>
        <div className="space-y-4">
          <Input label="Name" value={form.name} onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))} />
          <Textarea label="Description" value={form.description} onChange={(e) => setForm((p) => ({ ...p, description: e.target.value }))} />
          <Select
            label="Category"
            value={form.category_id}
            onChange={(e) => setForm((p) => ({ ...p, category_id: e.target.value }))}
            placeholder="Select category"
            options={categories.map((c) => ({ value: c.id, label: c.name }))}
          />
          <Input label="Price (₱)" type="number" step="0.01" value={form.price} onChange={(e) => setForm((p) => ({ ...p, price: e.target.value }))} />
          <div>
            <label className="mb-1 block text-sm font-medium text-stone-700">Image</label>
            {imagePreview && <img src={imagePreview} alt="" className="mb-2 h-32 w-full rounded-lg object-cover bg-stone-100" />}
            <input type="file" accept="image/*" onChange={handleImagePick} className="text-sm text-stone-600" />
          </div>
          <div className="flex gap-3 pt-2">
            <Button variant="secondary" onClick={() => setModalOpen(false)} className="flex-1">Cancel</Button>
            <Button onClick={handleSave} loading={saving} className="flex-1">{editing ? 'Update' : 'Create'}</Button>
          </div>
        </div>
      </Modal>
    </div>
  );
}
