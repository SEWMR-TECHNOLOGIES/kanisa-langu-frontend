import DataTable from "../../components/head-parish/DataTable";
import { mockProvinces } from "../../data/dioceseMockData";

export default function DioceseProvinces() {
  return (
    <DataTable
      title="All Provinces"
      description="View and manage all provinces under this diocese"
      columns={[
        { key: "name", label: "Province Name" },
        { key: "diocese", label: "Diocese" },
        { key: "region", label: "Region" },
        { key: "district", label: "District" },
        { key: "phone", label: "Phone" },
        { key: "email", label: "Email" },
        { key: "address", label: "Address" },
        { key: "status", label: "Status", render: (r: any) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${r.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockProvinces}
      searchPlaceholder="Search provinces..."
      searchKeys={["name", "diocese", "region"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
