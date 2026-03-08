import DataTable from "../../components/head-parish/DataTable";
import { mockPaymentWallets } from "../../data/headParishMockData";

export default function PaymentGatewayWallets() {
  return (
    <DataTable
      title="Payment Gateway Wallets"
      description="Manage mobile money wallets for payment collection"
      columns={[
        { key: "name", label: "Wallet Name" },
        { key: "wallet_number", label: "Wallet Number" },
        { key: "status", label: "Status", render: (r) => (
          <span className="px-2.5 py-1 rounded-full text-[11px] font-medium bg-admin-success/10 text-admin-success">{r.status}</span>
        )},
      ]}
      data={mockPaymentWallets}
      searchPlaceholder="Search wallets..."
      searchKeys={["name"]}
    />
  );
}
