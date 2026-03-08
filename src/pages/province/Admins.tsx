import FormCard from "../../components/head-parish/FormCard";

export default function ProvinceAdmins() {
  return (
    <FormCard
      title="Manage Province Admins"
      description="Update province administrator settings"
      submitLabel="Save Changes"
      fields={[
        { name: "name", label: "Admin Name", type: "text", placeholder: "Enter name", required: true },
        { name: "email", label: "Email", type: "email", placeholder: "Enter email", required: true },
        { name: "phone", label: "Phone", type: "tel", placeholder: "Enter phone" },
        { name: "role", label: "Role", type: "select", options: [
          { value: "admin", label: "Administrator" },
          { value: "moderator", label: "Moderator" },
          { value: "viewer", label: "Viewer" },
        ]},
      ]}
    />
  );
}
