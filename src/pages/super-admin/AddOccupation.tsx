import FormCard from "../../components/head-parish/FormCard";

export default function AddOccupation() {
  return (
    <FormCard
      title="Add Occupation"
      description="Add a new occupation to the system"
      submitLabel="Add Occupation"
      fields={[
        { name: "occupation_name", label: "Occupation Name", type: "text", placeholder: "Enter Occupation Name", required: true },
      ]}
    />
  );
}
