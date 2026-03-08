import { useState } from "react";
import { Outlet } from "react-router-dom";
import AppSidebar from "./AppSidebar";
import AppHeader from "./AppHeader";

export default function AppLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="min-h-screen bg-background">
      <AppSidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      <div className="xl:ml-[270px]">
        <AppHeader onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} />
        <main className="p-6 max-w-[1200px] mx-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
