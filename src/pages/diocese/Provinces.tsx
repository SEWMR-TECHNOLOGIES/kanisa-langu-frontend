import DataTable from "../../components/head-parish/DataTable";
import { mockProvinces } from "../../data/dioceseMockData";

export default function DioceseProvinces() {
  return (
    <DataTable
      title="All Provinces"
      description="View and manage all provinces under this diocese"
      columns={[
        { key: "name", label: "Province Name" },
        { key: "head_parishes", label: "Head Parishes" },
        { key: "total_members", label: "Total Members", render: (r: any) => <span className="tabular-nums">{Number(r.total_members).toLocaleString()}</span> },
        { key: "status", label: "Status", render: (r: any) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockProvinces}
      searchPlaceholder="Search provinces..."
      searchKeys={["name"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
