import FormCard from "../../components/head-parish/FormCard";

export default function SPCreateAdmin() {
  return (
    <FormCard
      title="Create Sub Parish Admin"
      description="Register a new administrator"
      submitLabel="Create Admin"
      fields={[
        { name: "name", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "email", label: "Email", type: "email", placeholder: "Enter email", required: true },
        { name: "phone", label: "Phone", type: "tel", placeholder: "Enter phone number" },
        { name: "role", label: "Role", type: "select", options: [
          { value: "admin", label: "Administrator" },
          { value: "moderator", label: "Moderator" },
        ]},
      ]}
    />
  );
}
