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
  onAction?: (action: string, row: T, tabId: string) => void;
}

export default function TabbedDataTable<T extends Record<string, any>>({
  title,
  description,
  tabs,
  searchPlaceholder,
  actions,
  onAction,
}: TabbedDataTableProps<T>) {
  const [activeTab, setActiveTab] = useState(tabs[0]?.id);
  const current = tabs.find((t) => t.id === activeTab) || tabs[0];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">{title}</h1>
        {description && <p className="text-sm text-admin-text mt-1">{description}</p>}
      </div>

      {/* Tabs - matching TabbedFormCard style */}
      <div className="admin-card rounded-2xl overflow-hidden">
        <div className="px-6 pt-6 border-b border-admin-border/30">
          <div className="flex gap-1 overflow-x-auto pb-0 scrollbar-none">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`relative px-4 py-2.5 text-sm font-medium whitespace-nowrap rounded-t-xl transition-all duration-200 ${
                  activeTab === tab.id
                    ? "text-admin-accent bg-admin-accent/5 border-b-2 border-admin-accent"
                    : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        {/* Table inside card */}
        {current && (
          <div className="p-0">
            <DataTable
              title=""
              columns={current.columns}
              data={current.data}
              searchPlaceholder={searchPlaceholder}
              searchKeys={current.searchKeys}
              actions={actions}
              onAction={onAction ? (action, row) => onAction(action, row, activeTab || "") : undefined}
            />
          </div>
        )}
      </div>
    </div>
  );
}
