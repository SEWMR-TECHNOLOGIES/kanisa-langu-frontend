import FormCard from "../../components/head-parish/FormCard";

const typeOptions = [
  { value: "head-parish", label: "Head Parish" }, { value: "sub-parish", label: "Sub Parish" },
  { value: "community", label: "Community" }, { value: "groups", label: "Groups" },
];
const harambeeOptions = [
  { value: "1", label: "Church Building - Jan 2025 - Dec 2025 - TZS 50,000,000" },
  { value: "2", label: "School Renovation - Mar 2025 - Sep 2025 - TZS 30,000,000" },
];
const memberOptions = [
  { value: "1", label: "Mch. Juma Mwangi - 0712345678 - Y26" },
  { value: "2", label: "Bi. Maria Kimaro - 0723456789 - Y45" },
  { value: "3", label: "Ndg. Peter Mushi - 0734567890 - Y12" },
];
const paymentMethodOptions = [
  { value: "Cash", label: "Cash" }, { value: "Bank Transfer", label: "Bank Transfer" },
  { value: "Mobile Payment", label: "Mobile Payment" }, { value: "Card", label: "Card" },
];

export default function RecordHarambeeContribution() {
  return (
    <FormCard
      title="Record Harambee Contribution"
      description="Record individual harambee contributions"
      submitLabel="Record Harambee Contribution"
      fields={[
        { name: "target_table", label: "Select Type", type: "select", required: true, options: typeOptions },
        { name: "harambee_id", label: "Harambee", type: "select", required: true, options: harambeeOptions },
        { name: "member_id", label: "Select Member", type: "select", required: true, options: memberOptions },
        { name: "amount", label: "Contribution Amount", type: "number", placeholder: "Amount", required: true },
        { name: "contribution_date", label: "Contribution Date", type: "date", required: true },
        { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
      ]}
    />
  );
}
