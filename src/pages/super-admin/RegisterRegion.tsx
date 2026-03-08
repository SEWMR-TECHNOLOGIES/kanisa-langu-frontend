import FormCard from "../../components/head-parish/FormCard";

export default function RegisterRegion() {
  return (
    <FormCard
      title="Register Region"
      description="Add a new region to the system"
      submitLabel="Register Region"
      fields={[
        { name: "region_name", label: "Region Name", type: "text", placeholder: "Enter Region Name", required: true },
      ]}
    />
  );
}
