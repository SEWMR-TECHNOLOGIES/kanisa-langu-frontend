import FormCard from "../../components/head-parish/FormCard";
import { mockBankAccounts } from "../../data/headParishMockData";

export default function RecordRevenue() {
  return (
    <FormCard
      title="Record Revenue"
      description="Record new revenue for the parish"
      submitLabel="Record Revenue"
      fields={[
        { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: [
          { value: "1", label: "Sadaka ya Ibada" }, { value: "2", label: "Zaka" },
          { value: "3", label: "Sadaka Maalum" }, { value: "4", label: "Ada ya Uanachama" },
        ]},
        { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
        { name: "date", label: "Date", type: "date", required: true },
        { name: "account_id", label: "Bank Account", type: "select", required: true, options: mockBankAccounts.map(a => ({ value: String(a.id), label: a.account_name || "" })) },
        { name: "description", label: "Description", type: "textarea", placeholder: "Optional description", colSpan: 2 },
      ]}
    />
  );
}
