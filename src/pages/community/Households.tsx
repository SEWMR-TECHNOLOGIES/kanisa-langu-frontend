import DataTable from "../../components/head-parish/DataTable";
import { mockHouseholds } from "../../data/communityMockData";

export default function CommunityHouseholds() {
  return (
    <DataTable
      title="Households"
      description="View and manage community households"
      columns={[
        { key: "name", label: "Household Name" },
        { key: "head", label: "Head of Household" },
        { key: "members_count", label: "Members" },
        { key: "location", label: "Location" },
      ]}
      data={mockHouseholds}
      searchPlaceholder="Search households..."
      searchKeys={["name", "head"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
