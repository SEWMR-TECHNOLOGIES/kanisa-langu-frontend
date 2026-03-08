import FormCard from "../../components/head-parish/FormCard";

const regionOptions = [
  { value: "1", label: "Kilimanjaro" }, { value: "2", label: "Arusha" },
  { value: "3", label: "Manyara" }, { value: "4", label: "Tanga" },
  { value: "5", label: "Dodoma" }, { value: "6", label: "Morogoro" },
];

export default function RegisterDistrict() {
  return (
    <FormCard
      title="Register District"
      description="Add a new district to the system"
      submitLabel="Register District"
      fields={[
        { name: "district_name", label: "District Name", type: "text", placeholder: "Enter District Name", required: true },
        { name: "region_id", label: "Region", type: "select", required: true, options: regionOptions },
      ]}
    />
  );
}
