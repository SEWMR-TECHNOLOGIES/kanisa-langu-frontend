import { Menu, Bell, User } from "lucide-react";
import { useAuth } from "@/contexts/AuthContext";

interface HeaderProps {
  onToggleSidebar: () => void;
}

export default function AppHeader({ onToggleSidebar }: HeaderProps) {
  const { user } = useAuth();

  return (
    <header className="h-[70px] bg-card border-b border-border flex items-center justify-between px-6 sticky top-0 z-30">
      <button
        onClick={onToggleSidebar}
        className="xl:hidden text-muted-foreground hover:text-foreground"
      >
        <Menu className="h-6 w-6" />
      </button>

      <div className="flex-1" />

      <div className="flex items-center gap-4">
        <button className="relative text-muted-foreground hover:text-foreground">
          <Bell className="h-5 w-5" />
        </button>
        <div className="flex items-center gap-2">
          <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
            <User className="h-4 w-4 text-primary" />
          </div>
          <span className="text-sm font-medium text-foreground hidden sm:block">
            {user?.username}
          </span>
        </div>
      </div>
    </header>
  );
}
