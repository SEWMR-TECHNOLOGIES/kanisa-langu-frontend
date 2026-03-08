import FormCard from "../../components/head-parish/FormCard";

export default function MakeExpenseRequest() {
  return (
    <FormCard
      title="Make Expense Request"
      description="Submit a new expense request for approval"
      submitLabel="Submit Request"
      fields={[
        { name: "expense_name", label: "Expense Name", type: "select", required: true, options: [
          { value: "1", label: "Office Supplies" }, { value: "2", label: "Fuel" },
          { value: "3", label: "Printing" }, { value: "4", label: "Electricity" },
          { value: "5", label: "Water Bill" }, { value: "6", label: "Internet" },
        ]},
        { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
        { name: "description", label: "Description", type: "textarea", placeholder: "Describe the expense request", colSpan: 2 },
      ]}
    />
  );
}
