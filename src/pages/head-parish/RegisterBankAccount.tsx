import FormCard from "../../components/head-parish/FormCard";
import { mockBankAccounts } from "../../data/headParishMockData";

export default function RegisterBankAccount() {
  return (
    <FormCard
      title="Register Bank Account"
      description="Add a new bank account for the parish"
      submitLabel="Register Account"
      fields={[
        { name: "account_name", label: "Account Name", type: "text", placeholder: "Enter account name", required: true },
        { name: "bank_name", label: "Bank Name", type: "text", placeholder: "Enter bank name", required: true },
        { name: "account_number", label: "Account Number", type: "text", placeholder: "Enter account number", required: true },
        { name: "opening_balance", label: "Opening Balance", type: "number", placeholder: "Enter opening balance" },
      ]}
    />
  );
}
