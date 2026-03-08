import FormCard from "../../components/head-parish/FormCard";

export default function CommunityScheduleMeeting() {
  return (
    <FormCard
      title="Schedule Meeting"
      description="Schedule a new community meeting"
      submitLabel="Schedule Meeting"
      fields={[
        { name: "title", label: "Meeting Title", type: "text", placeholder: "Enter meeting title", required: true },
        { name: "date", label: "Date", type: "date", required: true },
        { name: "venue", label: "Venue", type: "text", placeholder: "Enter venue" },
        { name: "expected_attendees", label: "Expected Attendees", type: "number", placeholder: "Number of attendees" },
        { name: "agenda", label: "Agenda", type: "textarea", placeholder: "Enter meeting agenda", colSpan: 2 },
      ]}
    />
  );
}
