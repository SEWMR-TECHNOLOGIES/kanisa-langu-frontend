import DataTable from "../../components/head-parish/DataTable";

const mockBanks = [
  { id: 1, bank_name: "CRDB Bank" },
  { id: 2, bank_name: "NMB Bank" },
  { id: 3, bank_name: "NBC Bank" },
  { id: 4, bank_name: "Stanbic Bank" },
  { id: 5, bank_name: "DTB Bank" },
  { id: 6, bank_name: "Equity Bank" },
  { id: 7, bank_name: "Exim Bank" },
  { id: 8, bank_name: "KCB Bank" },
];

export default function ManageBanks() {
  return (
    <DataTable
      title="Manage Banks"
      description="View and manage all registered banks"
      columns={[
        { key: "bank_name", label: "Bank Name" },
      ]}
      data={mockBanks}
      searchPlaceholder="Search banks..."
      searchKeys={["bank_name"]}
      actions={["edit", "delete"]}
    />
  );
}
