import FormCard from "../../components/head-parish/FormCard";

export default function ProvinceCreateHPAdmin() {
  return (
    <FormCard
      title="Create Head Parish Admin"
      description="Register a new administrator for a head parish"
      submitLabel="Register Admin"
      fields={[
        { name: "head_parish_admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "head_parish_admin_email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "head_parish_admin_phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number", required: true },
        { name: "head_parish_id", label: "Head Parish", type: "select", required: true, options: [
          { value: "1", label: "Msimbazi" },
          { value: "2", label: "Azania" },
          { value: "3", label: "Uhuru" },
          { value: "4", label: "Kariakoo" },
          { value: "5", label: "Buguruni" },
          { value: "6", label: "Ilala" },
          { value: "7", label: "Kijitonyama" },
        ]},
        { name: "head_parish_admin_role", label: "Admin Role", type: "select", required: true, options: [
          { value: "admin", label: "Admin" },
          { value: "pastor", label: "Pastor" },
          { value: "secretary", label: "Secretary" },
          { value: "chairperson", label: "Chairperson" },
        ]},
      ]}
    />
  );
}
