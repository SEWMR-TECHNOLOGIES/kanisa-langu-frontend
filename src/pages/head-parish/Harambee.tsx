import TabbedDataTable from "../../components/head-parish/TabbedDataTable";
import { mockHarambee } from "../../data/headParishMockData";

export default function Harambee() {
  return (
    <TabbedDataTable
      title="Manage Harambee"
      description="View and manage all harambee programs across levels"
      searchPlaceholder="Search harambee..."
      actions={["edit", "delete"]}
      tabs={[
        {
          id: "head-parish",
          label: "Head Parish",
          columns: [
            { key: "description", label: "Description" },
            { key: "from_date", label: "From Date" },
            { key: "to_date", label: "To Date" },
            { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent">TZS {r.amount}</span> },
            { key: "account_name", label: "Account Name" },
          ],
          data: mockHarambee.headParish,
          searchKeys: ["description"],
        },
        {
          id: "sub-parish",
          label: "Sub Parish",
          columns: [
            { key: "description", label: "Description" },
            { key: "from_date", label: "From Date" },
            { key: "to_date", label: "To Date" },
            { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent">TZS {r.amount}</span> },
            { key: "sub_parish_name", label: "Sub Parish" },
            { key: "account_name", label: "Account" },
          ],
          data: mockHarambee.subParish,
          searchKeys: ["description"],
        },
        {
          id: "community",
          label: "Community",
          columns: [
            { key: "description", label: "Description" },
            { key: "from_date", label: "From Date" },
            { key: "to_date", label: "To Date" },
            { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent">TZS {r.amount}</span> },
            { key: "community_name", label: "Community" },
            { key: "sub_parish_name", label: "Sub Parish" },
            { key: "account_name", label: "Account" },
          ],
          data: mockHarambee.community,
          searchKeys: ["description"],
        },
        {
          id: "group",
          label: "Group",
          columns: [
            { key: "description", label: "Description" },
            { key: "from_date", label: "From Date" },
            { key: "to_date", label: "To Date" },
            { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent">TZS {r.amount}</span> },
            { key: "group_name", label: "Group" },
            { key: "account_name", label: "Account" },
          ],
          data: mockHarambee.group,
          searchKeys: ["description"],
        },
      ]}
    />
  );
}
