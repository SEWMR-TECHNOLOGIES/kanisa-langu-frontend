import FormCard from "../../components/head-parish/FormCard";

export default function CommunitySendNotification() {
  return (
    <FormCard
      title="Send Notification"
      description="Send a notification to community members"
      submitLabel="Send Notification"
      fields={[
        { name: "title", label: "Notification Title", type: "text", placeholder: "Enter title", required: true },
        { name: "target", label: "Target Audience", type: "select", required: true, options: [
          { value: "all", label: "All Members" },
          { value: "household_heads", label: "Household Heads" },
          { value: "youth", label: "Youth" },
        ]},
        { name: "message", label: "Message", type: "textarea", placeholder: "Enter notification message", colSpan: 2 },
      ]}
    />
  );
}
