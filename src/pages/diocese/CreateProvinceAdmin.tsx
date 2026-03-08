import FormCard from "../../components/head-parish/FormCard";

export default function DioceseCreateProvinceAdmin() {
  return (
    <FormCard
      title="Create Province Admin"
      description="Register a new administrator for a province"
      submitLabel="Create Admin"
      fields={[
        { name: "name", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number" },
        { name: "province", label: "Province", type: "select", required: true, options: [
          { value: "Northern Province", label: "Northern Province" },
          { value: "Eastern Province", label: "Eastern Province" },
          { value: "Southern Province", label: "Southern Province" },
          { value: "Western Province", label: "Western Province" },
          { value: "Central Province", label: "Central Province" },
          { value: "Lake Province", label: "Lake Province" },
        ]},
      ]}
    />
  );
}
