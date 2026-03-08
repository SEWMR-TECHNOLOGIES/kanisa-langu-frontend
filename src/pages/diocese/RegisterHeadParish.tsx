import FormCard from "../../components/head-parish/FormCard";

const provinceOptions = [
  { value: "1", label: "Kilimanjaro" },
  { value: "2", label: "Arusha" },
  { value: "3", label: "Manyara" },
  { value: "4", label: "Pare" },
];

export default function DioceseRegisterHeadParish() {
  return (
    <FormCard
      title="Register Head Parish"
      description="Register a new head parish under this diocese"
      submitLabel="Register Head Parish"
      fields={[
        { name: "name", label: "Head Parish Name", type: "text", placeholder: "e.g. Usharika wa Moshi", required: true },
        { name: "province", label: "Province", type: "select", required: true, options: provinceOptions },
        { name: "location", label: "Location", type: "text", placeholder: "District/Town" },
        { name: "phone", label: "Phone", type: "tel", placeholder: "Contact phone" },
      ]}
    />
  );
}
