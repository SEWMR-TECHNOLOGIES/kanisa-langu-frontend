import FormCard from "../../components/head-parish/FormCard";

export default function ProvinceAdmins() {
  return (
    <FormCard
      title="Create Province Admin"
      description="Register a new province administrator"
      submitLabel="Register Admin"
      fields={[
        { name: "province_admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "province_admin_email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "province_admin_phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number", required: true },
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
