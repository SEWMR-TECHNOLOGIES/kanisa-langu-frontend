import DataTable from "../../components/head-parish/DataTable";
import { mockHeadParishes } from "../../data/provinceMockData";

export default function ProvinceHeadParishes() {
  return (
    <DataTable
      title="All Head Parishes"
      description="View and manage head parishes in this province"
      columns={[
        { key: "name", label: "Head Parish" },
        { key: "sub_parishes", label: "Sub Parishes" },
        { key: "members", label: "Members", render: (r) => <span className="tabular-nums">{r.members.toLocaleString()}</span> },
        { key: "status", label: "Status", render: (r) => (
          <span className="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-admin-success/10 text-admin-success">{r.status}</span>
        )},
      ]}
      data={mockHeadParishes}
      searchPlaceholder="Search head parishes..."
      searchKeys={["name"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
