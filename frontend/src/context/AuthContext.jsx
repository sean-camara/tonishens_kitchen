import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { getMe, login as apiLogin, register as apiRegister, logout as apiLogout } from '../api/auth';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchUser = useCallback(async () => {
    const token = localStorage.getItem('access_token');
    if (!token) { setLoading(false); return; }
    try {
      const { data } = await getMe();
      setUser(data.data || data.user);
    } catch {
      localStorage.removeItem('access_token');
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchUser(); }, [fetchUser]);

  const login = async (credentials) => {
    const { data } = await apiLogin(credentials);
    localStorage.setItem('access_token', data.access_token);
    setUser(data.user);
    return data;
  };

  const register = async (formData) => {
    const { data } = await apiRegister(formData);
    localStorage.setItem('access_token', data.access_token);
    setUser(data.user);
    return data;
  };

  const logout = async () => {
    try { await apiLogout(); } catch { /* ignore */ }
    localStorage.removeItem('access_token');
    setUser(null);
  };

  const isAdmin = user?.role === 'admin' || user?.role === 'super_admin';
  const isSuperAdmin = user?.role === 'super_admin';
  const isAuthenticated = !!user;

  return (
    <AuthContext.Provider value={{ user, setUser, loading, login, register, logout, isAdmin, isSuperAdmin, isAuthenticated, fetchUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
};
