import FormCard from "../../components/head-parish/FormCard";

export default function RegisterGroup() {
  return (
    <FormCard
      title="Register Group"
      description="Create a new church group"
      submitLabel="Register Group"
      fields={[
        { name: "name", label: "Group Name", type: "text", placeholder: "Enter group name", required: true },
        { name: "description", label: "Description", type: "textarea", placeholder: "Enter group description", colSpan: 2 },
      ]}
    />
  );
}
