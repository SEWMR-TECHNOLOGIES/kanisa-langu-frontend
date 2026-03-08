import DataTable from "../../components/head-parish/DataTable";
import { mockBankAccounts } from "../../data/headParishMockData";

export default function BankAccounts() {
  return (
    <DataTable
      title="Manage Bank Accounts"
      description="View and manage all bank accounts"
      columns={[
        { key: "account_name", label: "Account Name" },
        { key: "bank_name", label: "Bank Name" },
        { key: "account_number", label: "Account Number" },
        { key: "balance", label: "Opening Balance", render: (r) => <span className="font-medium text-admin-accent">TZS {r.balance}</span> },
      ]}
      data={mockBankAccounts}
      searchPlaceholder="Search bank accounts..."
      searchKeys={["account_name", "bank_name"]}
    />
  );
}
