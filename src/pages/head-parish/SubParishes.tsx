import DataTable from "../../components/head-parish/DataTable";
import { mockSubParishes } from "../../data/headParishMockData";

export default function SubParishes() {
  return (
    <DataTable
      title="Manage Sub Parishes"
      description="View and manage all sub parishes in your head parish"
      columns={[
        { key: "name", label: "Sub Parish Name" },
        { key: "description", label: "Description" },
      ]}
      data={mockSubParishes}
      searchPlaceholder="Search sub parishes by name..."
      searchKeys={["name"]}
    />
  );
}
