import DataTable from "../../components/head-parish/DataTable";
import { mockSPHarambee } from "../../data/subParishMockData";

export default function SPHarambee() {
  return (
    <DataTable
      title="Harambee"
      description="View and manage harambee programs"
      columns={[
        { key: "description", label: "Description" },
        { key: "target", label: "Target" },
        { key: "collected", label: "Collected" },
        { key: "progress", label: "Progress", render: (r: any) => (
          <div className="flex items-center gap-2">
            <div className="w-20 h-2 rounded-full bg-admin-surface-hover overflow-hidden">
              <div className="h-full rounded-full bg-admin-accent" style={{ width: `${r.progress}%` }} />
            </div>
            <span className="text-xs tabular-nums">{r.progress}%</span>
          </div>
        )},
      ]}
      data={mockSPHarambee}
      searchPlaceholder="Search harambee..."
      searchKeys={["description"]}
      actions={["view", "edit"]}
    />
  );
}
