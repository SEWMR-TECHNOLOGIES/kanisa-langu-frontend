import FormCard from "../../components/head-parish/FormCard";
import { mockHeadParishes } from "../../data/provinceMockData";

export default function ProvinceCreateHPAdmin() {
  return (
    <FormCard
      title="Create Head Parish Admin"
      description="Register a new administrator for a head parish"
      submitLabel="Create Admin"
      fields={[
        { name: "name", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number" },
        { name: "head_parish", label: "Head Parish", type: "select", required: true, options: mockHeadParishes.map(p => ({ value: p.name, label: p.name })) },
      ]}
    />
  );
}
