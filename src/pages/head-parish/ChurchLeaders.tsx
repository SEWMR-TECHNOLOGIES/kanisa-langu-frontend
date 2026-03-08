import DataTable from "../../components/head-parish/DataTable";
import { mockLeaders } from "../../data/headParishMockData";

export default function ChurchLeaders() {
  return (
    <DataTable
      title="Manage Church Leaders"
      description="View and manage all church leaders and their roles"
      columns={[
        { key: "name", label: "Full Name" },
        { key: "role", label: "Role" },
        { key: "appointment_date", label: "Appointment Date" },
        { key: "end_date", label: "End Date" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-destructive/10 text-destructive"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockLeaders}
      searchPlaceholder="Search leaders by name or role..."
      searchKeys={["name", "role"]}
      actions={["edit", "delete"]}
    />
  );
}
