import DataTable from "../../components/head-parish/DataTable";
import { mockSPMembers } from "../../data/subParishMockData";

export default function SPChurchMembers() {
  return (
    <DataTable
      title="Church Members"
      description="Manage all church members in this sub parish"
      columns={[
        { key: "name", label: "Full Name" },
        { key: "community", label: "Community" },
        { key: "phone", label: "Phone" },
        { key: "envelope", label: "Envelope #" },
        { key: "status", label: "Status", render: (r: any) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockSPMembers}
      searchPlaceholder="Search members..."
      searchKeys={["name", "community"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
