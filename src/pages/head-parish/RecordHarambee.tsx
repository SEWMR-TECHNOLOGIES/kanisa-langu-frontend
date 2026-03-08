import FormCard from "../../components/head-parish/FormCard";

export default function RecordHarambee() {
  return (
    <FormCard
      title="Record New Harambee"
      description="Create a new harambee program"
      submitLabel="Record Harambee"
      fields={[
        { name: "description", label: "Harambee Description", type: "text", placeholder: "e.g., Church Building Fund", required: true },
        { name: "target_level", label: "Target Level", type: "select", required: true, options: [
          { value: "head-parish", label: "Head Parish" },
          { value: "sub-parish", label: "Sub Parish" },
          { value: "community", label: "Community" },
          { value: "group", label: "Group" },
        ]},
        { name: "amount", label: "Target Amount (TZS)", type: "number", placeholder: "Enter target amount", required: true },
        { name: "account_id", label: "Bank Account", type: "select", required: true, options: [
          { value: "1", label: "Main Account - CRDB" },
          { value: "2", label: "Building Fund - NMB" },
          { value: "3", label: "Harambee Account - NBC" },
        ]},
        { name: "from_date", label: "Start Date", type: "date", required: true },
        { name: "to_date", label: "End Date", type: "date", required: true },
      ]}
    />
  );
}
