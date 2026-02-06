import { useState, useEffect, useCallback } from 'react';
import { PlusIcon, TrashIcon, PencilIcon } from '@heroicons/react/24/outline';
import { getAdminAbout, updateAboutHistory, saveContact, deleteContact, saveSocialLink, deleteSocialLink, saveFaq, deleteFaq } from '../../api/admin';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Textarea from '../../components/ui/Textarea';
import Spinner from '../../components/ui/Spinner';
import Card from '../../components/ui/Card';
import toast from 'react-hot-toast';

export default function AdminAbout() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [history, setHistory] = useState('');
  const [savingHistory, setSavingHistory] = useState(false);

  const load = useCallback(async () => {
    try {
      const { data } = await getAdminAbout();
      setData(data);
      setHistory(data.history || '');
    } catch { toast.error('Failed to load'); }
    finally { setLoading(false); }
  }, []);

  useEffect(() => { load(); }, [load]);

  const handleSaveHistory = async () => {
    setSavingHistory(true);
    try { await updateAboutHistory({ content: history }); toast.success('History saved'); }
    catch { toast.error('Failed'); }
    finally { setSavingHistory(false); }
  };

  const handleAddItem = async (type, formData) => {
    try {
      const fn = type === 'contact' ? saveContact : type === 'social' ? saveSocialLink : saveFaq;
      await fn(formData);
      load();
      toast.success('Added');
    } catch { toast.error('Failed'); }
  };

  const handleDeleteItem = async (type, id) => {
    if (!confirm('Delete?')) return;
    try {
      const fn = type === 'contact' ? deleteContact : type === 'social' ? deleteSocialLink : deleteFaq;
      await fn(id);
      load();
      toast.success('Deleted');
    } catch { toast.error('Failed'); }
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="max-w-3xl space-y-6">
      <h1 className="font-heading text-2xl font-bold text-stone-900">About Page CMS</h1>

      {/* History */}
      <Card>
        <Card.Header><h2 className="font-semibold text-stone-900">Store History</h2></Card.Header>
        <Card.Body className="space-y-3">
          <Textarea rows={8} value={history} onChange={(e) => setHistory(e.target.value)} />
          <Button onClick={handleSaveHistory} loading={savingHistory}>Save History</Button>
        </Card.Body>
      </Card>

      {/* Contacts */}
      <CmsSection
        title="Contact Info"
        items={data?.contacts || []}
        fields={['type', 'value']}
        labels={['Type (Email, Phone, etc.)', 'Value']}
        onAdd={(d) => handleAddItem('contact', d)}
        onDelete={(id) => handleDeleteItem('contact', id)}
      />

      {/* Social Links */}
      <CmsSection
        title="Social Media"
        items={data?.social_links || []}
        fields={['platform', 'url']}
        labels={['Platform', 'URL']}
        onAdd={(d) => handleAddItem('social', d)}
        onDelete={(id) => handleDeleteItem('social', id)}
      />

      {/* FAQs */}
      <CmsSection
        title="FAQs"
        items={data?.faqs || []}
        fields={['question', 'answer']}
        labels={['Question', 'Answer']}
        onAdd={(d) => handleAddItem('faq', d)}
        onDelete={(id) => handleDeleteItem('faq', id)}
        textareaField="answer"
      />
    </div>
  );
}

function CmsSection({ title, items, fields, labels, onAdd, onDelete, textareaField }) {
  const [show, setShow] = useState(false);
  const [form, setForm] = useState(Object.fromEntries(fields.map((f) => [f, ''])));

  const handleAdd = () => {
    onAdd(form);
    setForm(Object.fromEntries(fields.map((f) => [f, ''])));
    setShow(false);
  };

  return (
    <Card>
      <Card.Header className="flex items-center justify-between">
        <h2 className="font-semibold text-stone-900">{title}</h2>
        <Button variant="ghost" size="xs" onClick={() => setShow(!show)}><PlusIcon className="h-4 w-4" /></Button>
      </Card.Header>
      <Card.Body className="space-y-3">
        {show && (
          <div className="space-y-3 rounded-lg border border-stone-200 bg-stone-50 p-3">
            {fields.map((f, i) =>
              textareaField === f ? (
                <Textarea key={f} label={labels[i]} value={form[f]} onChange={(e) => setForm((p) => ({ ...p, [f]: e.target.value }))} />
              ) : (
                <Input key={f} label={labels[i]} value={form[f]} onChange={(e) => setForm((p) => ({ ...p, [f]: e.target.value }))} />
              )
            )}
            <div className="flex gap-2">
              <Button size="sm" onClick={handleAdd}>Add</Button>
              <Button variant="secondary" size="sm" onClick={() => setShow(false)}>Cancel</Button>
            </div>
          </div>
        )}
        {items.map((item) => (
          <div key={item.id} className="flex items-start justify-between gap-3 rounded-lg border border-stone-100 p-3">
            <div className="min-w-0">
              <p className="text-sm font-medium text-stone-800">{item[fields[0]]}</p>
              <p className="text-sm text-stone-500 break-all">{item[fields[1]]}</p>
            </div>
            <button onClick={() => onDelete(item.id)} className="shrink-0 rounded-lg p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-500">
              <TrashIcon className="h-4 w-4" />
            </button>
          </div>
        ))}
        {items.length === 0 && !show && <p className="text-sm text-stone-500 text-center py-2">No items yet</p>}
      </Card.Body>
    </Card>
  );
}
