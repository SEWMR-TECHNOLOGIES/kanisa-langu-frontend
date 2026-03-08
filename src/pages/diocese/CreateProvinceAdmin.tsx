import FormCard from "../../components/head-parish/FormCard";
import { mockProvinces } from "../../data/dioceseMockData";

export default function DioceseCreateProvinceAdmin() {
  return (
    <FormCard
      title="Create Province Admin"
      description="Register a new administrator for a province"
      submitLabel="Create Admin"
      fields={[
        { name: "name", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
        { name: "email", label: "Email Address", type: "email", placeholder: "Enter email", required: true },
        { name: "phone", label: "Phone Number", type: "tel", placeholder: "Enter phone number" },
        { name: "province", label: "Province", type: "select", required: true, options: mockProvinces.map(p => ({ value: p.name, label: p.name })) },
      ]}
    />
  );
}
