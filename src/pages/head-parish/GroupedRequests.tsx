import TabbedDataTable from "../../components/head-parish/TabbedDataTable";

const mockGrouped = {
  "head-parish": Array.from({ length: 5 }, (_, i) => ({
    id: i + 1,
    description: ["January Expenses", "February Operations", "March Repairs", "April Utilities", "May Supplies"][i],
    request_date: `2025-0${i + 1}-15`,
    account_name: ["Main Account - CRDB", "Building Fund - NMB"][i % 2],
    items_count: [4, 3, 6, 2, 5][i],
    total: [850000, 420000, 1200000, 310000, 680000][i],
  })),
  "sub-parish": Array.from({ length: 3 }, (_, i) => ({
    id: i + 1,
    description: ["Moshi Mjini Jan", "Hai Feb", "Rombo Mar"][i],
    request_date: `2025-0${i + 1}-20`,
    account_name: "Main Account - CRDB",
    items_count: [3, 2, 4][i],
    total: [250000, 180000, 450000][i],
  })),
  "community": Array.from({ length: 2 }, (_, i) => ({
    id: i + 1,
    description: ["Mwika Jan", "Marangu Feb"][i],
    request_date: `2025-0${i + 1}-25`,
    account_name: "Main Account - CRDB",
    items_count: [2, 3][i],
    total: [150000, 280000][i],
  })),
  "group": Array.from({ length: 2 }, (_, i) => ({
    id: i + 1,
    description: ["Vijana Q1", "Wanawake Q1"][i],
    request_date: `2025-0${i + 1}-28`,
    account_name: "Main Account - CRDB",
    items_count: [3, 2][i],
    total: [200000, 160000][i],
  })),
};

export default function GroupedRequests() {
  return (
    <TabbedDataTable
      title="Grouped Expense Requests"
      description="View grouped expense requests across management levels"
      searchPlaceholder="Search expense requests..."
      actions={["view", "edit"]}
      tabs={[
        {
          id: "head-parish",
          label: "Head Parish",
          columns: [
            { key: "description", label: "Description" },
            { key: "request_date", label: "Request Date" },
            { key: "account_name", label: "Account Name" },
            { key: "items_count", label: "Items" },
            { key: "total", label: "Total", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.total).toLocaleString()}</span> },
          ],
          data: mockGrouped["head-parish"],
          searchKeys: ["description"],
        },
        {
          id: "sub-parish",
          label: "Sub Parish",
          columns: [
            { key: "description", label: "Description" },
            { key: "request_date", label: "Request Date" },
            { key: "account_name", label: "Account Name" },
            { key: "items_count", label: "Items" },
            { key: "total", label: "Total", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.total).toLocaleString()}</span> },
          ],
          data: mockGrouped["sub-parish"],
          searchKeys: ["description"],
        },
        {
          id: "community",
          label: "Community",
          columns: [
            { key: "description", label: "Description" },
            { key: "request_date", label: "Request Date" },
            { key: "account_name", label: "Account Name" },
            { key: "items_count", label: "Items" },
            { key: "total", label: "Total", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.total).toLocaleString()}</span> },
          ],
          data: mockGrouped["community"],
          searchKeys: ["description"],
        },
        {
          id: "group",
          label: "Group",
          columns: [
            { key: "description", label: "Description" },
            { key: "request_date", label: "Request Date" },
            { key: "account_name", label: "Account Name" },
            { key: "items_count", label: "Items" },
            { key: "total", label: "Total", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.total).toLocaleString()}</span> },
          ],
          data: mockGrouped["group"],
          searchKeys: ["description"],
        },
      ]}
    />
  );
}
