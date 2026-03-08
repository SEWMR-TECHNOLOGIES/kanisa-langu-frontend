import FormCard from "../../components/head-parish/FormCard";

export default function ProvinceCreateHPAdmin() {
  return (
    <FormCard
      title="Create Head Parish Admin"
      description="Register a new administrator for a head parish"
      submitLabel="Create Admin"
      fields={[
        { name: "name", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number" },
        { name: "head_parish", label: "Head Parish", type: "select", required: true, options: [
          { value: "Msimbazi", label: "Msimbazi" },
          { value: "Azania", label: "Azania" },
          { value: "Uhuru", label: "Uhuru" },
          { value: "Kariakoo", label: "Kariakoo" },
          { value: "Buguruni", label: "Buguruni" },
          { value: "Ilala", label: "Ilala" },
          { value: "Kijitonyama", label: "Kijitonyama" },
        ]},
      ]}
    />
  );
}
