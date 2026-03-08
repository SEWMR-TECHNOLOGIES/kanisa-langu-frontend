import FormCard from "../../components/head-parish/FormCard";

const dioceseOptions = [
  { value: "1", label: "Kilimanjaro" }, { value: "2", label: "Arusha" },
  { value: "3", label: "Manyara" }, { value: "4", label: "Tanga" },
];

export default function RegisterProvince() {
  return (
    <FormCard
      title="Register Province"
      description="Add a new province to the system"
      submitLabel="Register Province"
      fields={[
        { name: "province_name", label: "Province Name", type: "text", placeholder: "Enter Province Name", required: true },
        { name: "diocese_id", label: "Diocese", type: "select", required: true, options: dioceseOptions },
      ]}
    />
  );
}
