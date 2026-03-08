import FormCard from "../../components/head-parish/FormCard";

const provinceOptions = [
  { value: "1", label: "Moshi" }, { value: "2", label: "Hai" },
  { value: "3", label: "Rombo" }, { value: "4", label: "Arusha Urban" },
];

export default function RegisterHeadParish() {
  return (
    <FormCard
      title="Register Head Parish"
      description="Add a new head parish to the system"
      submitLabel="Register Head Parish"
      fields={[
        { name: "head_parish_name", label: "Head Parish Name", type: "text", placeholder: "Enter Head Parish Name", required: true },
        { name: "province_id", label: "Province", type: "select", required: true, options: provinceOptions },
      ]}
    />
  );
}
