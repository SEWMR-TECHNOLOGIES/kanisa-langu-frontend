import FormCard from "../../components/head-parish/FormCard";
import { mockMembers } from "../../data/headParishMockData";

export default function RegisterChurchLeader() {
  return (
    <FormCard
      title="Register Church Leader"
      description="Assign a leadership role to a church member"
      submitLabel="Register Leader"
      fields={[
        { name: "member_id", label: "Church Member", type: "select", required: true, options: mockMembers.slice(0, 10).map(m => ({ value: String(m.id), label: `${m.first_name} ${m.last_name}` })) },
        { name: "role_id", label: "Leadership Role", type: "select", required: true, options: [
          { value: "1", label: "Parish Pastor" }, { value: "2", label: "Secretary" },
          { value: "3", label: "Accountant" }, { value: "4", label: "Evangelist" },
          { value: "5", label: "Elder" }, { value: "6", label: "Deacon" },
        ]},
        { name: "appointment_date", label: "Appointment Date", type: "date", required: true },
        { name: "end_date", label: "End Date", type: "date" },
      ]}
    />
  );
}
