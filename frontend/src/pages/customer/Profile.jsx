import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuth } from '../../context/AuthContext';
import { getProfile, updateProfile, updateAvatar, changePassword } from '../../api/profile';
import Input from '../../components/ui/Input';
import Textarea from '../../components/ui/Textarea';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import { UserCircleIcon, CameraIcon } from '@heroicons/react/24/outline';
import toast from 'react-hot-toast';

const profileSchema = z.object({
  first_name: z.string().min(1, 'Required'),
  last_name: z.string().min(1, 'Required'),
  phone: z.string().optional(),
  address: z.string().optional(),
});

const passwordSchema = z.object({
  current_password: z.string().min(1, 'Required'),
  password: z.string().min(8, 'At least 8 characters'),
  password_confirmation: z.string(),
}).refine((d) => d.password === d.password_confirmation, { message: 'Passwords must match', path: ['password_confirmation'] });

export default function Profile() {
  const { user, setUser, logout } = useAuth();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('profile');
  const [savingProfile, setSavingProfile] = useState(false);
  const [savingPassword, setSavingPassword] = useState(false);
  const [avatarPreview, setAvatarPreview] = useState(null);

  const profileForm = useForm({ resolver: zodResolver(profileSchema) });
  const passwordForm = useForm({ resolver: zodResolver(passwordSchema) });

  useEffect(() => {
    getProfile().then(({ data }) => {
      const u = data.user;
      profileForm.reset({ first_name: u.first_name, last_name: u.last_name, phone: u.phone || '', address: u.address || '' });
      setAvatarPreview(u.avatar_url);
    }).catch(() => {}).finally(() => setLoading(false));
  }, []);

  const handleProfileSave = async (data) => {
    setSavingProfile(true);
    try {
      const res = await updateProfile(data);
      setUser(res.data.user);
      toast.success('Profile updated');
    } catch { toast.error('Failed to update'); }
    finally { setSavingProfile(false); }
  };

  const handleAvatarChange = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { toast.error('Max 2MB'); return; }
    const fd = new FormData();
    fd.append('avatar', file);
    try {
      const res = await updateAvatar(fd);
      setAvatarPreview(res.data.avatar_url);
      setUser((prev) => ({ ...prev, avatar_url: res.data.avatar_url }));
      toast.success('Avatar updated');
    } catch { toast.error('Failed to upload'); }
  };

  const handlePasswordChange = async (data) => {
    setSavingPassword(true);
    try {
      await changePassword(data);
      passwordForm.reset();
      toast.success('Password changed');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed');
    } finally { setSavingPassword(false); }
  };

  const handleLogout = async () => {
    await logout();
    navigate('/sign-in');
  };

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  const tabs = [
    { key: 'profile', label: 'Profile' },
    { key: 'security', label: 'Security' },
  ];

  return (
    <div className="container-app py-8 sm:py-12 max-w-2xl">
      <h1 className="font-heading text-2xl font-bold text-stone-900 sm:text-3xl mb-6">My Account</h1>

      {/* Tabs */}
      <div className="flex gap-1 rounded-lg bg-stone-100 p-1 mb-6">
        {tabs.map((t) => (
          <button
            key={t.key}
            onClick={() => setTab(t.key)}
            className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
              tab === t.key ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'profile' && (
        <div className="rounded-xl border border-stone-200 bg-white p-5 shadow-sm space-y-6">
          {/* Avatar */}
          <div className="flex items-center gap-4">
            <div className="relative">
              {avatarPreview ? (
                <img src={avatarPreview} alt="" className="h-16 w-16 rounded-full object-cover border-2 border-stone-200" />
              ) : (
                <UserCircleIcon className="h-16 w-16 text-stone-300" />
              )}
              <label className="absolute -bottom-1 -right-1 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-primary-600 text-white shadow-md hover:bg-primary-700 transition-colors">
                <CameraIcon className="h-3.5 w-3.5" />
                <input type="file" accept="image/*" className="hidden" onChange={handleAvatarChange} />
              </label>
            </div>
            <div>
              <p className="font-semibold text-stone-900">{user?.first_name} {user?.last_name}</p>
              <p className="text-sm text-stone-500">{user?.email}</p>
            </div>
          </div>

          <form onSubmit={profileForm.handleSubmit(handleProfileSave)} className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <Input label="First Name" error={profileForm.formState.errors.first_name?.message} {...profileForm.register('first_name')} />
              <Input label="Last Name" error={profileForm.formState.errors.last_name?.message} {...profileForm.register('last_name')} />
            </div>
            <Input label="Phone" type="tel" {...profileForm.register('phone')} />
            <Textarea label="Address" {...profileForm.register('address')} />
            <Button type="submit" loading={savingProfile}>Save Changes</Button>
          </form>
        </div>
      )}

      {tab === 'security' && (
        <div className="space-y-6">
          <div className="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 className="text-lg font-semibold text-stone-900 mb-4">Change Password</h2>
            <form onSubmit={passwordForm.handleSubmit(handlePasswordChange)} className="space-y-4">
              <Input label="Current Password" type="password" error={passwordForm.formState.errors.current_password?.message} {...passwordForm.register('current_password')} />
              <Input label="New Password" type="password" error={passwordForm.formState.errors.password?.message} {...passwordForm.register('password')} />
              <Input label="Confirm New Password" type="password" error={passwordForm.formState.errors.password_confirmation?.message} {...passwordForm.register('password_confirmation')} />
              <Button type="submit" loading={savingPassword}>Update Password</Button>
            </form>
          </div>

          <div className="rounded-xl border border-red-200 bg-red-50 p-5">
            <h2 className="text-lg font-semibold text-red-800 mb-2">Sign Out</h2>
            <p className="text-sm text-red-600 mb-4">You'll need to sign in again to access your account.</p>
            <Button variant="danger" onClick={handleLogout}>Sign Out</Button>
          </div>
        </div>
      )}
    </div>
  );
}
