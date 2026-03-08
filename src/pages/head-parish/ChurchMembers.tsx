import DataTable from "../../components/head-parish/DataTable";
import { mockMembers } from "../../data/headParishMockData";

export default function ChurchMembers() {
  return (
    <DataTable
      title="Manage Church Members"
      description="View and manage all registered church members"
      columns={[
        { key: "full_name", label: "Full Name", render: (r) => `${r.title} ${r.first_name} ${r.middle_name} ${r.last_name}` },
        { key: "date_of_birth", label: "D.O.B" },
        { key: "phone", label: "Phone" },
        { key: "occupation", label: "Occupation" },
        { key: "sub_parish", label: "Sub Parish" },
        { key: "community", label: "Community" },
        { key: "type", label: "Type", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${r.type === "Mwenyeji" ? "bg-admin-success/10 text-admin-success" : "bg-admin-info/10 text-admin-info"}`}>
            {r.type}
          </span>
        )},
        { key: "envelope_number", label: "Env. No" },
      ]}
      data={mockMembers}
      searchPlaceholder="Search by name, phone, or envelope number..."
      searchKeys={["first_name", "last_name", "phone", "envelope_number"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
