import FormCard from "../../components/head-parish/FormCard";

export default function AddTitle() {
  return (
    <FormCard
      title="Add Title"
      description="Add a new title to the system"
      submitLabel="Add Title"
      fields={[
        { name: "title_name", label: "Title Name", type: "text", placeholder: "Enter Title (e.g., Rev., Dr., Prof.)", required: true },
      ]}
    />
  );
}
