import DataTable from "../../components/head-parish/DataTable";
import { mockHPAdmins } from "../../data/provinceMockData";

export default function ProvinceHPAdmins() {
  return (
    <DataTable
      title="Head Parish Admins"
      description="Manage head parish administrators"
      columns={[
        { key: "name", label: "Full Name" },
        { key: "head_parish", label: "Head Parish" },
        { key: "email", label: "Email" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockHPAdmins}
      searchPlaceholder="Search admins..."
      searchKeys={["name", "head_parish"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
