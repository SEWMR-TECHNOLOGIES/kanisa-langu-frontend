import FormCard from "../../components/head-parish/FormCard";

export default function SPRegisterMember() {
  return (
    <FormCard
      title="Register Church Member"
      description="Add a new member to this sub parish"
      submitLabel="Register Member"
      fields={[
        { name: "first_name", label: "First Name", type: "text", placeholder: "Enter first name", required: true },
        { name: "last_name", label: "Last Name", type: "text", placeholder: "Enter last name", required: true },
        { name: "phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number" },
        { name: "community", label: "Community", type: "select", required: true, options: [
          { value: "Mwika", label: "Mwika" }, { value: "Marangu", label: "Marangu" },
          { value: "Machame", label: "Machame" }, { value: "Kibosho", label: "Kibosho" },
        ]},
        { name: "date_of_birth", label: "Date of Birth", type: "date" },
        { name: "occupation", label: "Occupation", type: "text", placeholder: "Enter occupation" },
      ]}
    />
  );
}
