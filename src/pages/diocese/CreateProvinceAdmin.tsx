import FormCard from "../../components/head-parish/FormCard";

export default function DioceseCreateProvinceAdmin() {
  return (
    <FormCard
      title="Create Province Admin"
      description="Register a new administrator for a province"
      submitLabel="Register Admin"
      fields={[
        { name: "province_admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "province_admin_email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "province_admin_phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number", required: true },
        { name: "province_id", label: "Province", type: "select", required: true, options: [
          { value: "1", label: "Northern Province" },
          { value: "2", label: "Eastern Province" },
          { value: "3", label: "Southern Province" },
          { value: "4", label: "Western Province" },
          { value: "5", label: "Central Province" },
          { value: "6", label: "Lake Province" },
        ]},
        { name: "province_admin_role", label: "Admin Role", type: "select", required: true, options: [
          { value: "admin", label: "Admin" },
          { value: "bishop", label: "Bishop" },
          { value: "secretary", label: "Secretary" },
          { value: "chairperson", label: "Chairperson" },
        ]},
      ]}
    />
  );
}
