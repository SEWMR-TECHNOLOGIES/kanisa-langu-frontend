import DataTable from "../../components/head-parish/DataTable";
import { mockCommunityMembers } from "../../data/communityMockData";

export default function CommunityMembers() {
  return (
    <DataTable
      title="Community Members"
      description="View and manage community members"
      columns={[
        { key: "name", label: "Full Name" },
        { key: "household", label: "Household" },
        { key: "phone", label: "Phone" },
        { key: "status", label: "Status", render: (r: any) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockCommunityMembers}
      searchPlaceholder="Search members..."
      searchKeys={["name", "household"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
