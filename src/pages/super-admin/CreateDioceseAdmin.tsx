import FormCard from "../../components/head-parish/FormCard";

const dioceseOptions = [
  { value: "1", label: "Kilimanjaro" }, { value: "2", label: "Arusha" },
  { value: "3", label: "Manyara" }, { value: "4", label: "Tanga" },
  { value: "5", label: "Dodoma" }, { value: "6", label: "Morogoro" },
];

const roleOptions = [
  { value: "admin", label: "Admin" }, { value: "bishop", label: "Bishop" },
  { value: "secretary", label: "Secretary" }, { value: "chairperson", label: "Chairperson" },
];

export default function CreateDioceseAdmin() {
  return (
    <FormCard
      title="Create Diocese Admin"
      description="Add a new diocese administrator"
      submitLabel="Register Admin"
      fields={[
        { name: "diocese_admin_fullname", label: "Full Name", type: "text", placeholder: "Enter Full Name", required: true },
        { name: "diocese_admin_email", label: "Email", type: "email", placeholder: "Enter Email", required: true },
        { name: "diocese_admin_phone", label: "Phone Number", type: "tel", placeholder: "Enter Phone Number" },
        { name: "diocese_id", label: "Diocese", type: "select", required: true, options: dioceseOptions },
        { name: "diocese_admin_role", label: "Admin Role", type: "select", required: true, options: roleOptions },
      ]}
    />
  );
}
