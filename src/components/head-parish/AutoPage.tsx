import { useLocation } from "react-router-dom";
import DataTable from "./DataTable";
import FormCard from "./FormCard";
import type { PageConfig } from "../../data/pageConfigs";

interface AutoPageProps {
  configs: Record<string, PageConfig>;
}

export default function AutoPage({ configs }: AutoPageProps) {
  const location = useLocation();
  const slug = location.pathname.split("/").pop() || "";
  const config = configs[slug];

  if (!config) {
    const pageName = slug.replace(/-/g, " ").replace(/\b\w/g, c => c.toUpperCase());
    return (
      <FormCard
        title={pageName}
        description={`Manage ${pageName.toLowerCase()}`}
        submitLabel="Submit"
        fields={[
          { name: "name", label: "Name", type: "text" as const, placeholder: "Enter value", required: true },
        ]}
      />
    );
  }

  if (config.type === "form") {
    return (
      <FormCard
        title={config.title}
        description={config.description}
        submitLabel={config.submitLabel || "Submit"}
        fields={config.fields || []}
        infoBox={config.infoBox}
        statusPreview={config.statusPreview}
      />
    );
  }

  if (config.type === "table") {
    const columns = (config.columns || []).map(col => ({
      key: col.key,
      label: col.label,
      ...(col.type === "badge" ? {
        render: (row: any) => {
          const val = String(row[col.key] ?? "");
          const colors = col.badgeColors || {};
          const colorClass = colors[val] || "bg-admin-text/10 text-admin-text";
          return <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${colorClass}`}>{val}</span>;
        }
      } : col.type === "progress" ? {
        render: (row: any) => {
          const val = Number(row[col.key] ?? 0);
          return (
            <div className="flex items-center gap-2">
              <div className="w-20 h-2 rounded-full bg-admin-surface-hover overflow-hidden">
                <div className="h-full rounded-full bg-admin-accent transition-all" style={{ width: `${Math.min(val, 100)}%` }} />
              </div>
              <span className="text-xs tabular-nums text-admin-text-bright">{val}%</span>
            </div>
          );
        }
      } : col.type === "currency" ? {
        render: (row: any) => <span className="font-medium text-admin-accent tabular-nums">{String(row[col.key] ?? "")}</span>
      } : {}),
    }));

    return (
      <DataTable
        title={config.title}
        description={config.description}
        columns={columns}
        data={config.data || []}
        searchPlaceholder={config.searchPlaceholder || "Search..."}
        searchKeys={config.searchKeys as any || []}
        actions={config.actions || ["view", "edit"]}
      />
    );
  }

  return null;
}
