import React, { createContext, useContext, useState, useEffect, useCallback } from "react";

interface User {
  id: string;
  username: string;
  role?: string;
}

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  login: (username: string, password: string, recaptchaResponse?: string) => Promise<{ success: boolean; message: string }>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const stored = localStorage.getItem("kanisa_user");
    if (stored) {
      try {
        setUser(JSON.parse(stored));
      } catch {
        localStorage.removeItem("kanisa_user");
      }
    }
    setIsLoading(false);
  }, []);

  const login = useCallback(async (username: string, password: string, _recaptchaResponse?: string) => {
    const now = new Date();
    const localTime = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")} ${String(now.getHours()).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}:${String(now.getSeconds()).padStart(2, "0")}`;

    const formData = new FormData();
    formData.append("username", username);
    formData.append("password", password);
    formData.append("client_time", localTime);

    try {
      const res = await fetch("https://kanisalangu.sewmrtechnologies.com/api/kanisalangu_admin_signin.php", {
        method: "POST",
        credentials: "include",
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        const userData: User = {
          id: data.data?.admin_id || "1",
          username: data.data?.username || username,
          role: data.data?.role,
        };
        setUser(userData);
        localStorage.setItem("kanisa_user", JSON.stringify(userData));
      }

      return { success: data.success, message: data.message };
    } catch (error) {
      return { success: false, message: "Network error. Please try again." };
    }
  }, []);

  const logout = useCallback(() => {
    setUser(null);
    localStorage.removeItem("kanisa_user");
  }, []);

  return (
    <AuthContext.Provider value={{ user, isLoading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
