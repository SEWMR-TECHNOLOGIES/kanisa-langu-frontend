import DataTable from "../../components/head-parish/DataTable";
import { mockProvinceAdmins } from "../../data/dioceseMockData";

export default function DioceseProvinceAdmins() {
  return (
    <DataTable
      title="Province Admins"
      description="Manage province administrators"
      columns={[
        { key: "name", label: "Full Name" },
        { key: "province", label: "Province" },
        { key: "email", label: "Email" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockProvinceAdmins}
      searchPlaceholder="Search admins..."
      searchKeys={["name", "province"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
