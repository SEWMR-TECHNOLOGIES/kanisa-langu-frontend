import { useState, useMemo } from "react";
import { Search, ChevronLeft, ChevronRight, Edit2, Trash2, Eye, MoreHorizontal } from "lucide-react";
import { motion } from "framer-motion";

export interface Column<T> {
  key: keyof T | string;
  label: string;
  render?: (row: T) => React.ReactNode;
  className?: string;
}

interface DataTableProps<T> {
  title: string;
  description?: string;
  columns: Column<T>[];
  data: T[];
  searchPlaceholder?: string;
  searchKeys?: (keyof T)[];
  pageSize?: number;
  actions?: ("view" | "edit" | "delete")[];
  onAction?: (action: string, row: T) => void;
  headerRight?: React.ReactNode;
}

export default function DataTable<T extends Record<string, any>>({
  title,
  description,
  columns,
  data,
  searchPlaceholder = "Search...",
  searchKeys = [],
  pageSize = 10,
  actions = ["edit", "delete"],
  onAction,
  headerRight,
}: DataTableProps<T>) {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);

  const filtered = useMemo(() => {
    if (!search || searchKeys.length === 0) return data;
    const q = search.toLowerCase();
    return data.filter((row) =>
      searchKeys.some((key) => String(row[key]).toLowerCase().includes(q))
    );
  }, [data, search, searchKeys]);

  const totalPages = Math.ceil(filtered.length / pageSize);
  const paged = filtered.slice((page - 1) * pageSize, page * pageSize);

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-xl font-bold text-admin-text-bright font-display">{title}</h1>
          {description && <p className="text-sm text-admin-text mt-1">{description}</p>}
        </div>
        {headerRight}
      </div>

      {/* Card */}
      <div className="admin-card rounded-2xl overflow-hidden">
        {/* Search bar */}
        <div className="p-4 lg:p-6 border-b border-admin-border/30">
          <div className="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-admin-bg/60 border border-admin-border/30 max-w-md">
            <Search className="w-4 h-4 text-admin-text/50 flex-shrink-0" />
            <input
              type="text"
              placeholder={searchPlaceholder}
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              className="bg-transparent text-sm text-admin-text-bright placeholder:text-admin-text/40 outline-none w-full"
            />
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-admin-border/30">
                <th className="px-4 lg:px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">#</th>
                {columns.map((col) => (
                  <th key={String(col.key)} className={`px-4 lg:px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60 ${col.className || ""}`}>
                    {col.label}
                  </th>
                ))}
                {actions.length > 0 && (
                  <th className="px-4 lg:px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Actions</th>
                )}
              </tr>
            </thead>
            <tbody>
              {paged.map((row, idx) => (
                <motion.tr
                  key={idx}
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  transition={{ delay: idx * 0.02 }}
                  className="border-b border-admin-border/20 hover:bg-admin-surface-hover/50 transition-colors group"
                >
                  <td className="px-4 lg:px-6 py-3.5 text-sm text-admin-text/60 tabular-nums">
                    {(page - 1) * pageSize + idx + 1}
                  </td>
                  {columns.map((col) => (
                    <td key={String(col.key)} className={`px-4 lg:px-6 py-3.5 text-sm text-admin-text-bright ${col.className || ""}`}>
                      {col.render ? col.render(row) : String(row[col.key as keyof T] ?? "")}
                    </td>
                  ))}
                  {actions.length > 0 && (
                    <td className="px-4 lg:px-6 py-3.5 text-right">
                      <div className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        {actions.includes("view") && (
                          <button onClick={() => onAction?.("view", row)} className="p-2 rounded-lg hover:bg-admin-info/10 text-admin-info transition-colors">
                            <Eye className="w-4 h-4" />
                          </button>
                        )}
                        {actions.includes("edit") && (
                          <button onClick={() => onAction?.("edit", row)} className="p-2 rounded-lg hover:bg-admin-accent/10 text-admin-accent transition-colors">
                            <Edit2 className="w-4 h-4" />
                          </button>
                        )}
                        {actions.includes("delete") && (
                          <button onClick={() => onAction?.("delete", row)} className="p-2 rounded-lg hover:bg-destructive/10 text-destructive transition-colors">
                            <Trash2 className="w-4 h-4" />
                          </button>
                        )}
                      </div>
                    </td>
                  )}
                </motion.tr>
              ))}
              {paged.length === 0 && (
                <tr>
                  <td colSpan={columns.length + 2} className="px-6 py-16 text-center text-sm text-admin-text/50">
                    No records found
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="flex items-center justify-between px-4 lg:px-6 py-4 border-t border-admin-border/30">
            <p className="text-xs text-admin-text/50">
              Showing {(page - 1) * pageSize + 1}-{Math.min(page * pageSize, filtered.length)} of {filtered.length}
            </p>
            <div className="flex items-center gap-1">
              <button
                onClick={() => setPage(Math.max(1, page - 1))}
                disabled={page === 1}
                className="p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text disabled:opacity-30 transition-colors"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>
              {Array.from({ length: Math.min(totalPages, 5) }, (_, i) => {
                let pageNum: number;
                if (totalPages <= 5) pageNum = i + 1;
                else if (page <= 3) pageNum = i + 1;
                else if (page >= totalPages - 2) pageNum = totalPages - 4 + i;
                else pageNum = page - 2 + i;
                return (
                  <button
                    key={pageNum}
                    onClick={() => setPage(pageNum)}
                    className={`w-8 h-8 rounded-lg text-xs font-medium transition-colors ${
                      pageNum === page
                        ? "bg-admin-accent text-admin-bg"
                        : "text-admin-text hover:bg-admin-surface-hover"
                    }`}
                  >
                    {pageNum}
                  </button>
                );
              })}
              <button
                onClick={() => setPage(Math.min(totalPages, page + 1))}
                disabled={page === totalPages}
                className="p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text disabled:opacity-30 transition-colors"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
