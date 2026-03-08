import FormCard from "../../components/head-parish/FormCard";

export default function RegisterChurchChoir() {
  return (
    <FormCard
      title="Register Church Choir"
      description="Create a new church choir"
      submitLabel="Register Choir"
      fields={[
        { name: "name", label: "Choir Name", type: "text", placeholder: "Enter choir name", required: true },
        { name: "leader", label: "Choir Leader", type: "text", placeholder: "Enter leader name" },
        { name: "description", label: "Description", type: "textarea", placeholder: "Describe the choir", colSpan: 2 },
      ]}
    />
  );
}
