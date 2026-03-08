import FormCard from "../../components/head-parish/FormCard";
import { mockBankAccounts, mockSubParishes } from "../../data/headParishMockData";

export default function FinancialStatement() {
  return (
    <FormCard
      title="Financial Statement Report"
      description="Generate a financial statement for download"
      submitLabel="Download Report"
      fields={[
        { name: "account_id", label: "Bank Account", type: "select", required: true, options: mockBankAccounts.map(a => ({ value: String(a.id), label: `${a.account_name} - ${a.bank_name}` })) },
        { name: "start_date", label: "Start Date", type: "date", required: true },
        { name: "end_date", label: "End Date", type: "date", required: true },
        { name: "management_level", label: "Management Level", type: "select", required: true, options: [
          { value: "head-parish", label: "Head Parish" },
          { value: "sub-parish", label: "Sub Parish" },
          { value: "community", label: "Community" },
          { value: "group", label: "Group" },
        ]},
      ]}
    />
  );
}
