import FormCard from "../../components/head-parish/FormCard";

export default function RecordSundayService() {
  return (
    <FormCard
      title="Record Sunday Service"
      description="Record details of a Sunday service"
      submitLabel="Record Service"
      fields={[
        { name: "service_date", label: "Service Date", type: "date", required: true },
        { name: "scripture", label: "Base Scripture", type: "text", placeholder: "e.g., Mathayo 5:1-12", required: true },
        { name: "color", label: "Liturgical Color", type: "select", required: true, options: [
          { value: "Green", label: "Green" }, { value: "White", label: "White" },
          { value: "Purple", label: "Purple" }, { value: "Red", label: "Red" },
          { value: "Blue", label: "Blue" },
        ]},
        { name: "preacher", label: "Preacher", type: "text", placeholder: "Name of preacher" },
        { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional notes", colSpan: 2 },
      ]}
    />
  );
}
