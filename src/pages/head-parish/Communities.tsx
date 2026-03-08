import DataTable from "../../components/head-parish/DataTable";
import { mockCommunities } from "../../data/headParishMockData";

export default function Communities() {
  return (
    <DataTable
      title="Manage Communities"
      description="View and manage all communities across sub parishes"
      columns={[
        { key: "name", label: "Community Name" },
        { key: "sub_parish", label: "Sub Parish" },
        { key: "description", label: "Description" },
      ]}
      data={mockCommunities}
      searchPlaceholder="Search communities..."
      searchKeys={["name", "sub_parish"]}
    />
  );
}
