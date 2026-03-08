import FormCard from "../../components/head-parish/FormCard";

const regionOptions = [
  { value: "1", label: "Kilimanjaro" }, { value: "2", label: "Arusha" },
  { value: "3", label: "Manyara" }, { value: "4", label: "Tanga" },
  { value: "5", label: "Dodoma" }, { value: "6", label: "Morogoro" },
];

const districtOptions = [
  { value: "1", label: "Moshi Urban" }, { value: "2", label: "Moshi Rural" },
  { value: "3", label: "Hai" }, { value: "4", label: "Rombo" },
  { value: "5", label: "Same" }, { value: "6", label: "Mwanga" },
];

export default function RegisterDiocese() {
  return (
    <FormCard
      title="Register Diocese"
      description="Add a new diocese to the system"
      submitLabel="Register Diocese"
      fields={[
        { name: "diocese_name", label: "Diocese Name", type: "text", placeholder: "Enter Diocese Name", required: true },
        { name: "diocese_email", label: "Diocese Email", type: "email", placeholder: "Enter Diocese Email" },
        { name: "diocese_phone", label: "Diocese Phone", type: "tel", placeholder: "Enter Diocese Phone" },
        { name: "diocese_address", label: "Diocese Address", type: "text", placeholder: "Enter Diocese Address" },
        { name: "region_id", label: "Region", type: "select", required: true, options: regionOptions },
        { name: "district_id", label: "District", type: "select", required: true, options: districtOptions },
      ]}
    />
  );
}
