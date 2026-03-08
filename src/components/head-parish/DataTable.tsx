import { useState, useMemo } from "react";
import { Search, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Edit2, Trash2, Eye, Check } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import ModernSelect from "./ModernSelect";
import EditRecordModal from "./EditRecordModal";

export interface Column<T> {
  key: keyof T | string;
  label: string;
  render?: (row: T) => React.ReactNode;
  className?: string;
  editable?: boolean;
  type?: "text" | "number" | "date" | "select";
  options?: { value: string; label: string }[];
}

interface CustomAction<T> {
  label: string;
  icon: React.ElementType;
  className?: string;
  onClick: (row: T) => void;
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
  customActions?: CustomAction<T>[];
  onAction?: (action: string, row: T) => void;
  onEdit?: (row: T, updated: Partial<T>) => void;
  onDelete?: (row: T) => void;
  headerRight?: React.ReactNode;
  selectable?: boolean;
}

export default function DataTable<T extends Record<string, any>>({
  title,
  description,
  columns,
  data,
  searchPlaceholder = "Search...",
  searchKeys = [],
  pageSize: defaultPageSize = 10,
  actions = ["edit", "delete"],
  customActions = [],
  onAction,
  onEdit,
  onDelete,
  headerRight,
  selectable = true,
}: DataTableProps<T>) {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(defaultPageSize);
  const [selected, setSelected] = useState<Set<number>>(new Set());
  const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);
  
  // Edit modal state
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [editingRecord, setEditingRecord] = useState<T | null>(null);

  const filtered = useMemo(() => {
    if (!search || searchKeys.length === 0) return data;
    const q = search.toLowerCase();
    return data.filter((row) =>
      searchKeys.some((key) => String(row[key]).toLowerCase().includes(q))
    );
  }, [data, search, searchKeys]);

  const totalPages = Math.ceil(filtered.length / pageSize);
  const paged = filtered.slice((page - 1) * pageSize, page * pageSize);

  const allPageSelected = paged.length > 0 && paged.every((_, idx) => selected.has((page - 1) * pageSize + idx));

  const toggleAll = () => {
    const newSet = new Set(selected);
    if (allPageSelected) {
      paged.forEach((_, idx) => newSet.delete((page - 1) * pageSize + idx));
    } else {
      paged.forEach((_, idx) => newSet.add((page - 1) * pageSize + idx));
    }
    setSelected(newSet);
  };

  const toggleRow = (globalIdx: number) => {
    const newSet = new Set(selected);
    if (newSet.has(globalIdx)) newSet.delete(globalIdx);
    else newSet.add(globalIdx);
    setSelected(newSet);
  };

  const openEditModal = (row: T) => {
    setEditingRecord(row);
    setEditModalOpen(true);
  };

  const handleEditSave = (updated: Partial<T>) => {
    if (editingRecord) {
      onEdit?.(editingRecord, updated);
      onAction?.("edit", { ...editingRecord, ...updated });
    }
  };

  const confirmDelete = (row: T) => {
    onDelete?.(row);
    onAction?.("delete", row);
    setDeleteConfirm(null);
  };

  const pageSizes = [5, 10, 20, 50];

  return (
    <div className="space-y-6">
      {/* Header */}
      {(title || headerRight) && (
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            {title && <h1 className="text-xl font-bold text-admin-text-bright font-display">{title}</h1>}
            {description && <p className="text-sm text-admin-text mt-1">{description}</p>}
          </div>
          {headerRight}
        </div>
      )}

      {/* Card */}
      <div className="admin-card rounded-2xl overflow-hidden">
        {/* Toolbar */}
        <div className="p-4 lg:p-6 border-b border-admin-border/30 flex flex-col sm:flex-row sm:items-center gap-3">
          <div className="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-admin-bg/60 border border-admin-border/30 flex-1 max-w-md">
            <Search className="w-4 h-4 text-admin-text/50 flex-shrink-0" />
            <input
              type="text"
              placeholder={searchPlaceholder}
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              className="bg-transparent text-sm text-admin-text-bright placeholder:text-admin-text/40 outline-none w-full"
            />
          </div>
          {selected.size > 0 && (
            <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-admin-accent/10 text-admin-accent text-xs font-medium">
              <Check className="w-3.5 h-3.5" />
              {selected.size} selected
            </div>
          )}
          <div className="flex items-center gap-2 ml-auto">
            <span className="text-xs text-admin-text/60">Rows:</span>
            <ModernSelect
              options={pageSizes.map(s => ({ value: String(s), label: String(s) }))}
              value={String(pageSize)}
              onChange={(val) => { setPageSize(Number(val)); setPage(1); }}
              className="w-20"
            />
          </div>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-admin-border/30">
                {selectable && (
                  <th className="px-4 lg:px-6 py-3 w-12">
                    <label className="relative flex items-center justify-center cursor-pointer">
                      <input
                        type="checkbox"
                        checked={allPageSelected}
                        onChange={toggleAll}
                        className="sr-only peer"
                      />
                      <div className="w-[18px] h-[18px] rounded-md border-2 border-admin-border peer-checked:border-admin-accent peer-checked:bg-admin-accent transition-all duration-200 flex items-center justify-center">
                        {allPageSelected && <Check className="w-3 h-3 text-white" strokeWidth={3} />}
                      </div>
                    </label>
                  </th>
                )}
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
              {paged.map((row, idx) => {
                const globalIdx = (page - 1) * pageSize + idx;
                const isSelected = selected.has(globalIdx);
                const isDeleting = deleteConfirm === globalIdx;

                return (
                  <motion.tr
                    key={globalIdx}
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: idx * 0.02 }}
                    className={`border-b border-admin-border/20 transition-colors group ${
                      isSelected ? "bg-admin-accent/5" : "hover:bg-admin-surface-hover/50"
                    }`}
                  >
                    {selectable && (
                      <td className="px-4 lg:px-6 py-3.5 w-12">
                        <label className="relative flex items-center justify-center cursor-pointer">
                          <input
                            type="checkbox"
                            checked={isSelected}
                            onChange={() => toggleRow(globalIdx)}
                            className="sr-only peer"
                          />
                          <div className="w-[18px] h-[18px] rounded-md border-2 border-admin-border peer-checked:border-admin-accent peer-checked:bg-admin-accent transition-all duration-200 flex items-center justify-center">
                            {isSelected && <Check className="w-3 h-3 text-white" strokeWidth={3} />}
                          </div>
                        </label>
                      </td>
                    )}
                    <td className="px-4 lg:px-6 py-3.5 text-sm text-admin-text/60 tabular-nums">
                      {globalIdx + 1}
                    </td>
                    {columns.map((col) => (
                      <td key={String(col.key)} className={`px-4 lg:px-6 py-3.5 text-sm text-admin-text-bright ${col.className || ""}`}>
                        {col.render ? col.render(row) : String(row[col.key as keyof T] ?? "")}
                      </td>
                    ))}
                    {(actions.length > 0 || customActions.length > 0) && (
                      <td className="px-4 lg:px-6 py-3.5 text-right">
                        <AnimatePresence mode="wait">
                          {isDeleting ? (
                            <motion.div
                              key="deleting"
                              initial={{ opacity: 0, scale: 0.9 }}
                              animate={{ opacity: 1, scale: 1 }}
                              exit={{ opacity: 0, scale: 0.9 }}
                              className="flex items-center justify-end gap-1"
                            >
                              <span className="text-xs text-destructive mr-1">Delete?</span>
                              <button onClick={() => confirmDelete(row)} className="px-2 py-1.5 rounded-lg bg-destructive text-white text-xs font-medium hover:opacity-90 transition-opacity">
                                Yes
                              </button>
                              <button onClick={() => setDeleteConfirm(null)} className="px-2 py-1.5 rounded-lg bg-admin-surface-hover text-admin-text text-xs font-medium hover:bg-admin-border transition-colors">
                                No
                              </button>
                            </motion.div>
                          ) : (
                            <motion.div
                              key="actions"
                              className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                              {actions.includes("view") && (
                                <button onClick={() => onAction?.("view", row)} className="p-2 rounded-lg hover:bg-admin-info/10 text-admin-info transition-colors" title="View">
                                  <Eye className="w-4 h-4" />
                                </button>
                              )}
                              {actions.includes("edit") && (
                                <button onClick={() => openEditModal(row)} className="p-2 rounded-lg hover:bg-admin-accent/10 text-admin-accent transition-colors" title="Edit">
                                  <Edit2 className="w-4 h-4" />
                                </button>
                              )}
                              {actions.includes("delete") && (
                                <button onClick={() => setDeleteConfirm(globalIdx)} className="p-2 rounded-lg hover:bg-destructive/10 text-destructive transition-colors" title="Delete">
                                  <Trash2 className="w-4 h-4" />
                                </button>
                              )}
                            </motion.div>
                          )}
                        </AnimatePresence>
                      </td>
                    )}
                  </motion.tr>
                );
              })}
              {paged.length === 0 && (
                <tr>
                  <td colSpan={columns.length + (selectable ? 3 : 2)} className="px-6 py-16 text-center text-sm text-admin-text/50">
                    No records found
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Modern Pagination */}
        <div className="flex flex-col sm:flex-row items-center justify-between px-4 lg:px-6 py-4 border-t border-admin-border/30 gap-3">
          <p className="text-xs text-admin-text/50">
            Showing {filtered.length === 0 ? 0 : (page - 1) * pageSize + 1}–{Math.min(page * pageSize, filtered.length)} of {filtered.length} entries
          </p>
          <div className="flex items-center gap-1">
            <button onClick={() => setPage(1)} disabled={page === 1} className="p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text disabled:opacity-30 transition-colors" title="First page">
              <ChevronsLeft className="w-4 h-4" />
            </button>
            <button onClick={() => setPage(Math.max(1, page - 1))} disabled={page === 1} className="p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text disabled:opacity-30 transition-colors" title="Previous page">
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
                  className={`w-9 h-9 rounded-xl text-xs font-semibold transition-all duration-200 ${
                    pageNum === page
                      ? "bg-admin-accent text-white shadow-md shadow-admin-accent/25"
                      : "text-admin-text hover:bg-admin-surface-hover"
                  }`}
                >
                  {pageNum}
                </button>
              );
            })}
            <button onClick={() => setPage(Math.min(totalPages, page + 1))} disabled={page === totalPages || totalPages === 0} className="p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text disabled:opacity-30 transition-colors" title="Next page">
              <ChevronRight className="w-4 h-4" />
            </button>
            <button onClick={() => setPage(totalPages)} disabled={page === totalPages || totalPages === 0} className="p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text disabled:opacity-30 transition-colors" title="Last page">
              <ChevronsRight className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      {/* Edit Modal */}
      <EditRecordModal
        isOpen={editModalOpen}
        onClose={() => { setEditModalOpen(false); setEditingRecord(null); }}
        onSave={handleEditSave}
        row={editingRecord}
        columns={columns}
        title={`Edit ${title ? title.replace(/s$/, "") : "Record"}`}
      />
    </div>
  );
}
