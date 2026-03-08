import DataTable from "../../components/head-parish/DataTable";
import { mockExcludedMembers } from "../../data/headParishMockData";

export default function ExcludedChurchMembers() {
  return (
    <DataTable
      title="Excluded Church Members"
      description="Members who have been excluded from the parish"
      columns={[
        { key: "name", label: "Member Name" },
        { key: "reason", label: "Reason" },
        { key: "date", label: "Date Excluded" },
        { key: "excluded_by", label: "Excluded By" },
      ]}
      data={mockExcludedMembers}
      searchPlaceholder="Search excluded members..."
      searchKeys={["name", "reason"]}
      actions={["view"]}
    />
  );
}
