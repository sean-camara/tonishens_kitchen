import { useState, useEffect, useCallback } from 'react';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { getIngredients, createIngredient, updateIngredient, deleteIngredient, getIngredientCategories, createIngredientCategory } from '../../api/admin';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Select from '../../components/ui/Select';
import Modal from '../../components/ui/Modal';
import Spinner from '../../components/ui/Spinner';
import toast from 'react-hot-toast';

export default function Inventory() {
  const [ingredients, setIngredients] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ name: '', category_id: '', unit: '', quantity: '', reorder_level: '', cost_per_unit: '' });
  const [newCat, setNewCat] = useState('');
  const [showCatInput, setShowCatInput] = useState(false);

  const load = useCallback(async () => {
    try {
      const [iRes, cRes] = await Promise.all([getIngredients(), getIngredientCategories()]);
      setIngredients(iRes.data.data || iRes.data);
      setCategories(cRes.data.data || cRes.data);
    } catch { toast.error('Failed to load'); }
    finally { setLoading(false); }
  }, []);

  useEffect(() => { load(); }, [load]);

  const openAdd = () => {
    setEditing(null);
    setForm({ name: '', category_id: '', unit: '', quantity: '0', reorder_level: '0', cost_per_unit: '0' });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditing(item);
    setForm({ name: item.name, category_id: item.category_id, unit: item.unit, quantity: item.quantity, reorder_level: item.reorder_level, cost_per_unit: item.cost_per_unit });
    setModalOpen(true);
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      editing ? await updateIngredient(editing.id, form) : await createIngredient(form);
      toast.success(editing ? 'Updated' : 'Created');
      setModalOpen(false);
      load();
    } catch { toast.error('Failed'); }
    finally { setSaving(false); }
  };

  const handleDelete = async (id) => {
    if (!confirm('Delete?')) return;
    try { await deleteIngredient(id); load(); toast.success('Deleted'); } catch { toast.error('Failed'); }
  };

  const handleAddCategory = async () => {
    if (!newCat.trim()) return;
    try {
      await createIngredientCategory({ name: newCat });
      setNewCat('');
      setShowCatInput(false);
      load();
      toast.success('Category added');
    } catch { toast.error('Failed'); }
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="font-heading text-2xl font-bold text-stone-900">Inventory</h1>
        <Button onClick={openAdd} size="sm"><PlusIcon className="h-4 w-4" /> Add Item</Button>
      </div>

      <div className="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm min-w-[600px]">
            <thead className="border-b border-stone-200 bg-stone-50">
              <tr>
                <th className="px-4 py-3 font-medium text-stone-600">Name</th>
                <th className="px-4 py-3 font-medium text-stone-600">Category</th>
                <th className="px-4 py-3 font-medium text-stone-600">Stock</th>
                <th className="px-4 py-3 font-medium text-stone-600">Reorder</th>
                <th className="px-4 py-3 font-medium text-stone-600">Cost/Unit</th>
                <th className="px-4 py-3 font-medium text-stone-600 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-stone-100">
              {ingredients.map((item) => {
                const isLow = Number(item.quantity) <= Number(item.reorder_level);
                return (
                  <tr key={item.id} className="hover:bg-stone-50 transition-colors">
                    <td className="px-4 py-3 font-medium text-stone-800">{item.name}</td>
                    <td className="px-4 py-3 text-stone-600">{item.category?.name || '—'}</td>
                    <td className="px-4 py-3">
                      <span className={`font-medium ${isLow ? 'text-red-600' : 'text-stone-800'}`}>
                        {item.quantity} {item.unit}
                      </span>
                      {isLow && <span className="ml-1 text-xs text-red-500">Low</span>}
                    </td>
                    <td className="px-4 py-3 text-stone-500">{item.reorder_level}</td>
                    <td className="px-4 py-3 text-stone-600">₱{Number(item.cost_per_unit).toFixed(2)}</td>
                    <td className="px-4 py-3 text-right">
                      <div className="flex justify-end gap-1">
                        <button onClick={() => openEdit(item)} className="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600"><PencilIcon className="h-4 w-4" /></button>
                        <button onClick={() => handleDelete(item.id)} className="rounded-lg p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-500"><TrashIcon className="h-4 w-4" /></button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Edit Item' : 'Add Item'}>
        <div className="space-y-4">
          <Input label="Name" value={form.name} onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))} />
          <div className="flex gap-2 items-end">
            <Select
              label="Category"
              value={form.category_id}
              onChange={(e) => setForm((p) => ({ ...p, category_id: e.target.value }))}
              placeholder="Select"
              options={categories.map((c) => ({ value: c.id, label: c.name }))}
              className="flex-1"
            />
            <Button variant="ghost" size="sm" onClick={() => setShowCatInput(!showCatInput)}>+</Button>
          </div>
          {showCatInput && (
            <div className="flex gap-2">
              <Input placeholder="New category" value={newCat} onChange={(e) => setNewCat(e.target.value)} className="flex-1" />
              <Button size="sm" onClick={handleAddCategory}>Add</Button>
            </div>
          )}
          <div className="grid grid-cols-2 gap-3">
            <Input label="Unit" value={form.unit} onChange={(e) => setForm((p) => ({ ...p, unit: e.target.value }))} placeholder="e.g. pcs, kg" />
            <Input label="Quantity" type="number" value={form.quantity} onChange={(e) => setForm((p) => ({ ...p, quantity: e.target.value }))} />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <Input label="Reorder Level" type="number" value={form.reorder_level} onChange={(e) => setForm((p) => ({ ...p, reorder_level: e.target.value }))} />
            <Input label="Cost per Unit" type="number" step="0.01" value={form.cost_per_unit} onChange={(e) => setForm((p) => ({ ...p, cost_per_unit: e.target.value }))} />
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
