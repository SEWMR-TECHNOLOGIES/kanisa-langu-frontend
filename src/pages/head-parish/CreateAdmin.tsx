import FormCard from "../../components/head-parish/FormCard";

export default function CreateAdmin() {
  return (
    <FormCard
      title="Create System User"
      description="Add a new admin user with specific role permissions"
      submitLabel="Create User"
      fields={[
        { name: "first_name", label: "First Name", type: "text", placeholder: "Enter first name", required: true },
        { name: "last_name", label: "Last Name", type: "text", placeholder: "Enter last name", required: true },
        { name: "email", label: "Email", type: "email", placeholder: "Enter email address", required: true },
        { name: "phone", label: "Phone", type: "tel", placeholder: "0XXXXXXXXX", required: true },
        { name: "role", label: "Role", type: "select", required: true, options: [
          { value: "admin", label: "Admin" },
          { value: "secretary", label: "Secretary" },
          { value: "accountant", label: "Accountant" },
          { value: "clerk", label: "Clerk" },
          { value: "pastor", label: "Pastor" },
          { value: "evangelist", label: "Evangelist" },
        ]},
        { name: "password", label: "Password", type: "text", placeholder: "Enter password", required: true },
      ]}
    />
  );
}
