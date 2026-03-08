import DataTable from "../../components/head-parish/DataTable";
import { mockServices } from "../../data/headParishMockData";

export default function SundayServices() {
  return (
    <DataTable
      title="Sunday Services"
      description="View all recorded Sunday services"
      columns={[
        { key: "service_date", label: "Service Date" },
        { key: "scripture", label: "Scripture" },
        { key: "color", label: "Color", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${
            r.color === "Green" ? "bg-admin-success/10 text-admin-success" :
            r.color === "Purple" ? "bg-purple-500/10 text-purple-400" :
            r.color === "Red" ? "bg-destructive/10 text-destructive" :
            r.color === "Blue" ? "bg-admin-info/10 text-admin-info" :
            "bg-admin-text/10 text-admin-text"
          }`}>
            {r.color}
          </span>
        )},
      ]}
      data={mockServices}
      searchPlaceholder="Search by scripture or color..."
      searchKeys={["scripture", "color"]}
      actions={["view", "delete"]}
    />
  );
}
