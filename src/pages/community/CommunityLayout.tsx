import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Users, LogOut, CalendarDays, Bell
} from "lucide-react";
import type { NavSection } from "../../components/elct/AdminSidebar";

const BASE = "/elct/community";

const navigation: NavSection[] = [
  {
    title: "Home",
    items: [{ label: "Dashboard", icon: LayoutDashboard, href: BASE }],
  },
  {
    title: "Community",
    items: [
      {
        label: "Members", icon: Users,
        children: [
          { label: "All Members", href: `${BASE}/members` },
          { label: "Households", href: `${BASE}/households` },
        ],
      },
      {
        label: "Meetings", icon: CalendarDays,
        children: [
          { label: "Schedule Meeting", href: `${BASE}/schedule-meeting` },
          { label: "All Meetings", href: `${BASE}/meetings` },
        ],
      },
      {
        label: "Notifications", icon: Bell,
        children: [
          { label: "Send Notification", href: `${BASE}/send-notification` },
        ],
      },
    ],
  },
  {
    title: "Auth",
    items: [{ label: "Sign Out", icon: LogOut, href: "/" }],
  },
];

export default function CommunityLayout() {
  return <AdminLayout navigation={navigation} levelLabel="Community" basePath={BASE} />;
}
