import FormCard from "../../components/head-parish/FormCard";
import { mockSubParishes } from "../../data/headParishMockData";

export default function RegisterChurchMember() {
  return (
    <FormCard
      title="Register Church Member"
      description="Add a new member to the church"
      submitLabel="Register Member"
      fields={[
        { name: "title_id", label: "Title", type: "select", options: [
          { value: "1", label: "Mch." }, { value: "2", label: "Bi." }, { value: "3", label: "Ndg." }, { value: "4", label: "Dkt." }
        ]},
        { name: "first_name", label: "First Name", type: "text", placeholder: "Enter first name", required: true },
        { name: "middle_name", label: "Middle Name", type: "text", placeholder: "Enter middle name" },
        { name: "last_name", label: "Last Name", type: "text", placeholder: "Enter last name", required: true },
        { name: "date_of_birth", label: "Date of Birth", type: "date", required: true },
        { name: "gender", label: "Gender", type: "select", required: true, options: [
          { value: "Male", label: "Male" }, { value: "Female", label: "Female" }
        ]},
        { name: "type", label: "Type", type: "select", required: true, options: [
          { value: "Mgeni", label: "Mgeni" }, { value: "Mwenyeji", label: "Mwenyeji" }
        ]},
        { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: mockSubParishes.map(s => ({ value: String(s.id), label: s.name })) },
        { name: "community_id", label: "Community", type: "select", required: true, options: [
          { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" }, { value: "3", label: "Machame" }
        ]},
        { name: "occupation_id", label: "Occupation", type: "select", options: [
          { value: "1", label: "Teacher" }, { value: "2", label: "Farmer" }, { value: "3", label: "Doctor" }, { value: "4", label: "Engineer" }
        ]},
        { name: "phone", label: "Phone", type: "tel", placeholder: "0XXXXXXXXX" },
        { name: "email", label: "Email", type: "email", placeholder: "Enter email" },
        { name: "envelope_number", label: "Envelope Number", type: "text", placeholder: "e.g., Y26" },
      ]}
    />
  );
}
