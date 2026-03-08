import DataTable from "../../components/head-parish/DataTable";
import { mockDioceseAdmins } from "../../data/dioceseMockData";

export default function DioceseAdmins() {
  return (
    <DataTable
      title="Diocese Admins"
      description="Manage diocese administrators"
      columns={[
        { key: "name", label: "Admin Name" },
        { key: "email", label: "Email" },
        { key: "phone", label: "Phone" },
        { key: "diocese", label: "Diocese" },
        { key: "role", label: "Role", render: (r: any) => (
          <span className="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-admin-accent/10 text-admin-accent capitalize">
            {r.role || "Admin"}
          </span>
        )},
        { key: "status", label: "Status", render: (r: any) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockDioceseAdmins}
      searchPlaceholder="Search by name or diocese..."
      searchKeys={["name", "diocese", "role"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
