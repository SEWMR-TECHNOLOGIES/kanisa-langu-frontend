import DataTable from "../../components/head-parish/DataTable";
import { mockProvinceAdmins } from "../../data/provinceMockData";

export default function ProvinceAdminsList() {
  return (
    <DataTable
      title="Province Admins"
      description="Manage province administrators"
      columns={[
        { key: "name", label: "Admin Name" },
        { key: "email", label: "Email" },
        { key: "phone", label: "Phone" },
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
      data={mockProvinceAdmins}
      searchPlaceholder="Search admins..."
      searchKeys={["name", "role"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
