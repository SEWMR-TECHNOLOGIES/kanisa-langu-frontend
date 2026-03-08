import DataTable from "../../components/head-parish/DataTable";
import { mockHPAdmins } from "../../data/provinceMockData";

export default function ProvinceHPAdmins() {
  return (
    <DataTable
      title="Head Parish Admins"
      description="Manage head parish administrators"
      columns={[
        { key: "name", label: "Admin Name" },
        { key: "email", label: "Email" },
        { key: "phone", label: "Phone" },
        { key: "head_parish", label: "Head Parish" },
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
      data={mockHPAdmins}
      searchPlaceholder="Search by name or parish..."
      searchKeys={["name", "head_parish"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
