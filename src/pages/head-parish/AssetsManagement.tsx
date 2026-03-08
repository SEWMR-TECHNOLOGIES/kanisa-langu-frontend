import DataTable from "../../components/head-parish/DataTable";
import { mockAssets } from "../../data/headParishMockData";

export default function AssetsManagement() {
  return (
    <DataTable
      title="Assets Management"
      description="View and manage all parish assets"
      columns={[
        { key: "name", label: "Asset Name" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${
            r.status === "Active" ? "bg-admin-success/10 text-admin-success" :
            r.status === "Under Repair" ? "bg-admin-warning/10 text-admin-warning" :
            "bg-destructive/10 text-destructive"
          }`}>{r.status}</span>
        )},
        { key: "value", label: "Value", render: (r) => <span className="font-medium text-admin-accent">TZS {r.value}</span> },
      ]}
      data={mockAssets}
      searchPlaceholder="Search assets..."
      searchKeys={["name"]}
    />
  );
}
