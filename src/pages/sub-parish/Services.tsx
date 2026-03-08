import DataTable from "../../components/head-parish/DataTable";
import { mockSPServices } from "../../data/subParishMockData";

export default function SPServices() {
  return (
    <DataTable
      title="Sunday Services"
      description="View all recorded sunday services"
      columns={[
        { key: "date", label: "Date" },
        { key: "attendance", label: "Attendance" },
        { key: "offering", label: "Offering" },
      ]}
      data={mockSPServices}
      searchPlaceholder="Search services..."
      searchKeys={["date"]}
      actions={["view", "edit"]}
    />
  );
}
