import DataTable from "../../components/head-parish/DataTable";
import { mockDioceseAdmins } from "../../data/dioceseMockData";

export default function DioceseAdmins() {
  return (
    <DataTable
      title="Diocese Admins"
      description="Manage diocese administrators"
      columns={[
        { key: "name", label: "Full Name" },
        { key: "role", label: "Role" },
        { key: "email", label: "Email" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockDioceseAdmins}
      searchPlaceholder="Search admins..."
      searchKeys={["name", "role"]}
      actions={["view", "edit"]}
    />
  );
}
