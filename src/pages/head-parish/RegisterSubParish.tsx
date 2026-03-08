import FormCard from "../../components/head-parish/FormCard";

export default function RegisterSubParish() {
  return (
    <FormCard
      title="Register Sub Parish"
      description="Add a new sub parish to your head parish"
      submitLabel="Register Sub Parish"
      fields={[
        { name: "name", label: "Sub Parish Name", type: "text", placeholder: "Enter sub parish name", required: true },
        { name: "description", label: "Description", type: "textarea", placeholder: "Enter description", colSpan: 2 },
      ]}
    />
  );
}
