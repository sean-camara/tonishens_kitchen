import { useState, useEffect, useCallback } from 'react';
import { PlusIcon, TrashIcon } from '@heroicons/react/24/outline';
import { getAdmins, createAdmin, deleteAdmin } from '../../api/admin';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Modal from '../../components/ui/Modal';
import Spinner from '../../components/ui/Spinner';
import Card from '../../components/ui/Card';
import toast from 'react-hot-toast';

export default function Accounts() {
  const [admins, setAdmins] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ first_name: '', last_name: '', email: '', password: '', password_confirmation: '' });

  const load = useCallback(async () => {
    try { const { data } = await getAdmins(); setAdmins(data.data || data); }
    catch { toast.error('Failed to load'); }
    finally { setLoading(false); }
  }, []);

  useEffect(() => { load(); }, [load]);

  const handleCreate = async () => {
    setSaving(true);
    try {
      await createAdmin(form);
      toast.success('Admin created');
      setModalOpen(false);
      setForm({ first_name: '', last_name: '', email: '', password: '', password_confirmation: '' });
      load();
    } catch { toast.error('Failed to create'); }
    finally { setSaving(false); }
  };

  const handleDelete = async (id) => {
    if (!confirm('Delete this admin?')) return;
    try { await deleteAdmin(id); load(); toast.success('Deleted'); } catch { toast.error('Failed'); }
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="max-w-3xl space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="font-heading text-2xl font-bold text-stone-900">Admin Accounts</h1>
        <Button onClick={() => setModalOpen(true)} size="sm"><PlusIcon className="h-4 w-4" /> Add Admin</Button>
      </div>

      <div className="space-y-3">
        {admins.map((admin) => (
          <Card key={admin.id}>
            <Card.Body className="flex items-center justify-between gap-3">
              <div className="flex items-center gap-3 min-w-0">
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-600 font-bold text-sm shrink-0">
                  {admin.first_name?.[0]}{admin.last_name?.[0]}
                </div>
                <div className="min-w-0">
                  <div className="flex items-center gap-2">
                    <p className="font-medium text-stone-800 truncate">{admin.first_name} {admin.last_name}</p>
                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ${
                      admin.role === 'super_admin' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-600'
                    }`}>
                      {admin.role === 'super_admin' ? 'Super Admin' : 'Admin'}
                    </span>
                  </div>
                  <p className="text-sm text-stone-500 truncate">{admin.email}</p>
                </div>
              </div>
              <button onClick={() => handleDelete(admin.id)} className="rounded-lg p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-500 shrink-0" title={admin.role === 'super_admin' ? 'Cannot delete super admin' : 'Delete admin'} disabled={admin.role === 'super_admin'}>
                <TrashIcon className={`h-4 w-4 ${admin.role === 'super_admin' ? 'opacity-30' : ''}`} />
              </button>
            </Card.Body>
          </Card>
        ))}
        {admins.length === 0 && <p className="text-center text-stone-500 py-8">No admin accounts</p>}
      </div>

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title="Add Admin">
        <div className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <Input label="First Name" value={form.first_name} onChange={(e) => setForm((p) => ({ ...p, first_name: e.target.value }))} />
            <Input label="Last Name" value={form.last_name} onChange={(e) => setForm((p) => ({ ...p, last_name: e.target.value }))} />
          </div>
          <Input label="Email" type="email" value={form.email} onChange={(e) => setForm((p) => ({ ...p, email: e.target.value }))} />
          <Input label="Password" type="password" value={form.password} onChange={(e) => setForm((p) => ({ ...p, password: e.target.value }))} />
          <Input label="Confirm Password" type="password" value={form.password_confirmation} onChange={(e) => setForm((p) => ({ ...p, password_confirmation: e.target.value }))} />
          <div className="flex gap-3 pt-2">
            <Button variant="secondary" onClick={() => setModalOpen(false)} className="flex-1">Cancel</Button>
            <Button onClick={handleCreate} loading={saving} className="flex-1">Create Admin</Button>
          </div>
        </div>
      </Modal>
    </div>
  );
}
