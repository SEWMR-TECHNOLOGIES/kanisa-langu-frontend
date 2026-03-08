import { useState } from "react";
import { motion } from "framer-motion";
import DataTable, { Column } from "./DataTable";

interface Tab<T> {
  id: string;
  label: string;
  columns: Column<T>[];
  data: T[];
  searchKeys?: (keyof T)[];
}

interface TabbedDataTableProps<T> {
  title: string;
  description?: string;
  tabs: Tab<T>[];
  searchPlaceholder?: string;
  actions?: ("view" | "edit" | "delete")[];
}

export default function TabbedDataTable<T extends Record<string, any>>({
  title,
  description,
  tabs,
  searchPlaceholder,
  actions,
}: TabbedDataTableProps<T>) {
  const [activeTab, setActiveTab] = useState(tabs[0]?.id);
  const current = tabs.find((t) => t.id === activeTab) || tabs[0];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">{title}</h1>
        {description && <p className="text-sm text-admin-text mt-1">{description}</p>}
      </div>

      {/* Tabs */}
      <div className="flex items-center gap-1 p-1 rounded-xl bg-admin-surface/60 border border-admin-border/30 w-fit">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`relative px-4 py-2 rounded-lg text-xs font-medium transition-all ${
              activeTab === tab.id
                ? "text-admin-bg"
                : "text-admin-text hover:text-admin-text-bright"
            }`}
          >
            {activeTab === tab.id && (
              <motion.div
                layoutId="tab-indicator"
                className="absolute inset-0 rounded-lg bg-admin-accent"
                transition={{ type: "spring", bounce: 0.2, duration: 0.4 }}
              />
            )}
            <span className="relative z-10">{tab.label}</span>
          </button>
        ))}
      </div>

      {/* Table */}
      {current && (
        <DataTable
          title=""
          columns={current.columns}
          data={current.data}
          searchPlaceholder={searchPlaceholder}
          searchKeys={current.searchKeys}
          actions={actions}
        />
      )}
    </div>
  );
}
