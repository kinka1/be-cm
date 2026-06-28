import { createContext, useCallback, useContext, useMemo, useState } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { authApi } from '../api/endpoints';
import { getApiError, tokenStore, userStore } from '../api/client';
import type { User } from '../types/api';
import { useToast } from './toast';

interface AuthContextValue {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  login: (payload: { username: string; password: string }) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState(() => tokenStore.get());
  const [user, setUser] = useState<User | null>(() => userStore.get<User>());
  const queryClient = useQueryClient();
  const toast = useToast();

  const loginMutation = useMutation({
    mutationFn: authApi.login,
    onSuccess: ({ token: nextToken, user: nextUser }) => {
      tokenStore.set(nextToken);
      userStore.set(nextUser);
      setToken(nextToken);
      setUser(nextUser);
      toast.success('Login berhasil');
    },
    onError: (error) => toast.error(getApiError(error)),
  });

  const logout = useCallback(async () => {
    try {
      if (tokenStore.get()) await authApi.logout();
    } catch {
      // Logout lokal tetap dilakukan bila token sudah tidak valid di backend.
    } finally {
      tokenStore.clear();
      userStore.clear();
      setToken(null);
      setUser(null);
      queryClient.clear();
    }
  }, [queryClient]);

  const login = useCallback(async (payload: { username: string; password: string }) => {
    await loginMutation.mutateAsync(payload);
  }, [loginMutation]);

  const value = useMemo<AuthContextValue>(() => ({
    user,
    token,
    isAuthenticated: Boolean(token),
    login,
    logout,
  }), [user, token, login, logout]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used inside AuthProvider');
  return context;
}
