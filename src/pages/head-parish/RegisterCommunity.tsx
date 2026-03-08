import FormCard from "../../components/head-parish/FormCard";
import { mockSubParishes } from "../../data/headParishMockData";

export default function RegisterCommunity() {
  return (
    <FormCard
      title="Register Community"
      description="Add a new community to a sub parish"
      submitLabel="Register Community"
      fields={[
        { name: "name", label: "Community Name", type: "text", placeholder: "Enter community name", required: true },
        { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: mockSubParishes.map(s => ({ value: String(s.id), label: s.name })) },
        { name: "description", label: "Description", type: "textarea", placeholder: "Enter description", colSpan: 2 },
      ]}
    />
  );
}
