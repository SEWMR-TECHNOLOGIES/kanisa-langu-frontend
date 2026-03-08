import { useState } from "react";
import { motion } from "framer-motion";
import { User, Mail, Phone, Shield, Camera, Key, Bell, Globe, Save } from "lucide-react";
import ModernSelect from "../../components/head-parish/ModernSelect";

const tabs = [
  { id: "general", label: "General", icon: User },
  { id: "security", label: "Security", icon: Key },
  { id: "notifications", label: "Notifications", icon: Bell },
];

export default function Profile() {
  const [activeTab, setActiveTab] = useState("general");
  const [notifications, setNotifications] = useState({
    email: true,
    push: true,
    sms: false,
    weeklyReport: true,
  });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">My Profile</h1>
        <p className="text-sm text-admin-text mt-1">Manage your account settings and preferences</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {/* Profile Card */}
        <motion.div
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          className="admin-card rounded-2xl p-6 text-center"
        >
          <div className="relative inline-block mb-4">
            <div className="w-24 h-24 rounded-2xl bg-gradient-to-br from-admin-accent to-amber-600 flex items-center justify-center mx-auto">
              <span className="text-3xl font-bold text-white">A</span>
            </div>
            <button className="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-admin-surface border-2 border-admin-border flex items-center justify-center hover:bg-admin-surface-hover transition-colors">
              <Camera className="w-3.5 h-3.5 text-admin-text" />
            </button>
          </div>
          <h2 className="text-sm font-bold text-admin-text-bright">Admin User</h2>
          <p className="text-xs text-admin-text mt-0.5">admin@kanisalangu.com</p>
          <div className="mt-3 px-3 py-1.5 rounded-full bg-admin-accent/10 text-admin-accent text-[11px] font-semibold inline-flex items-center gap-1.5">
            <Shield className="w-3 h-3" />
            Administrator
          </div>
          <div className="mt-6 pt-4 border-t border-admin-border/30 space-y-3 text-left">
            <div className="flex items-center gap-3 text-xs">
              <Mail className="w-4 h-4 text-admin-text/50" />
              <span className="text-admin-text-bright">admin@kanisalangu.com</span>
            </div>
            <div className="flex items-center gap-3 text-xs">
              <Phone className="w-4 h-4 text-admin-text/50" />
              <span className="text-admin-text-bright">+255 712 345 678</span>
            </div>
            <div className="flex items-center gap-3 text-xs">
              <Globe className="w-4 h-4 text-admin-text/50" />
              <span className="text-admin-text-bright">Head Parish Level</span>
            </div>
          </div>
        </motion.div>

        {/* Settings Area */}
        <motion.div
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="lg:col-span-3 admin-card rounded-2xl overflow-hidden"
        >
          {/* Tab Bar */}
          <div className="px-6 pt-6 border-b border-admin-border/30">
            <div className="flex gap-1 overflow-x-auto pb-0 scrollbar-none">
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`relative px-4 py-2.5 text-sm font-medium whitespace-nowrap rounded-t-xl transition-all duration-200 flex items-center gap-2 ${
                    activeTab === tab.id
                      ? "text-admin-accent bg-admin-accent/5 border-b-2 border-admin-accent"
                      : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                  }`}
                >
                  <tab.icon className="w-4 h-4" />
                  {tab.label}
                </button>
              ))}
            </div>
          </div>

          {/* Content */}
          <div className="p-6 lg:p-8">
            {activeTab === "general" && (
              <motion.div
                key="general"
                initial={{ opacity: 0, x: 8 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-6"
              >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">First Name</label>
                    <input type="text" defaultValue="Admin" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Last Name</label>
                    <input type="text" defaultValue="User" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Email</label>
                    <input type="email" defaultValue="admin@kanisalangu.com" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Phone</label>
                    <input type="tel" defaultValue="+255 712 345 678" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Role</label>
                    <input type="text" value="Administrator" readOnly className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all opacity-60 cursor-not-allowed" />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Language</label>
                    <ModernSelect
                      options={[
                        { value: "sw", label: "Kiswahili" },
                        { value: "en", label: "English" },
                      ]}
                      value="en"
                      onChange={() => {}}
                    />
                  </div>
                </div>
                <div className="pt-2">
                  <button className="px-8 py-3 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold flex items-center gap-2">
                    <Save className="w-4 h-4" />
                    Save Changes
                  </button>
                </div>
              </motion.div>
            )}

            {activeTab === "security" && (
              <motion.div
                key="security"
                initial={{ opacity: 0, x: 8 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-6"
              >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Current Password</label>
                    <input type="password" placeholder="Enter current password" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                  <div />
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">New Password</label>
                    <input type="password" placeholder="Enter new password" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Confirm New Password</label>
                    <input type="password" placeholder="Confirm new password" className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all" />
                  </div>
                </div>
                <div className="pt-2">
                  <button className="px-8 py-3 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold flex items-center gap-2">
                    <Key className="w-4 h-4" />
                    Update Password
                  </button>
                </div>
              </motion.div>
            )}

            {activeTab === "notifications" && (
              <motion.div
                key="notifications"
                initial={{ opacity: 0, x: 8 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-5"
              >
                {[
                  { key: "email", label: "Email Notifications", desc: "Receive email alerts for important actions" },
                  { key: "push", label: "Push Notifications", desc: "Get browser push notifications" },
                  { key: "sms", label: "SMS Notifications", desc: "Receive SMS for critical alerts" },
                  { key: "weeklyReport", label: "Weekly Report", desc: "Receive a weekly parish summary email" },
                ].map((item) => (
                  <div key={item.key} className="flex items-center justify-between p-4 rounded-xl bg-admin-bg/50 border border-admin-border/20">
                    <div>
                      <p className="text-sm font-medium text-admin-text-bright">{item.label}</p>
                      <p className="text-xs text-admin-text mt-0.5">{item.desc}</p>
                    </div>
                    <button
                      onClick={() => setNotifications(prev => ({ ...prev, [item.key]: !prev[item.key as keyof typeof prev] }))}
                      className={`relative w-11 h-6 rounded-full transition-colors duration-200 ${
                        notifications[item.key as keyof typeof notifications] ? "bg-admin-accent" : "bg-admin-border"
                      }`}
                    >
                      <motion.div
                        animate={{ x: notifications[item.key as keyof typeof notifications] ? 20 : 2 }}
                        transition={{ type: "spring", stiffness: 500, damping: 30 }}
                        className="absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm"
                      />
                    </button>
                  </div>
                ))}
                <div className="pt-2">
                  <button className="px-8 py-3 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold flex items-center gap-2">
                    <Save className="w-4 h-4" />
                    Save Preferences
                  </button>
                </div>
              </motion.div>
            )}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
