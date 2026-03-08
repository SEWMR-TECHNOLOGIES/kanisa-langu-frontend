import FormCard from "../../components/head-parish/FormCard";

export default function NewChurchEvent() {
  return (
    <FormCard
      title="Create Church Event"
      description="Schedule a new church event"
      submitLabel="Create Event"
      fields={[
        { name: "title", label: "Event Title", type: "text", placeholder: "Enter event title", required: true },
        { name: "date", label: "Event Date", type: "date", required: true },
        { name: "location", label: "Location", type: "text", placeholder: "Enter location" },
        { name: "description", label: "Description", type: "textarea", placeholder: "Describe the event", colSpan: 2 },
      ]}
    />
  );
}
