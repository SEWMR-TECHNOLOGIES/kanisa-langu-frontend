import FormCard from "../../components/head-parish/FormCard";

export default function RegisterBank() {
  return (
    <FormCard
      title="Register Bank"
      description="Add a new bank to the system"
      submitLabel="Register Bank"
      fields={[
        { name: "bank_name", label: "Bank Name", type: "text", placeholder: "Enter Bank Name", required: true },
      ]}
    />
  );
}
