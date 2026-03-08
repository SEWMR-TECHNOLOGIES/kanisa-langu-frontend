import FormCard from "../../components/head-parish/FormCard";

export default function SendPushNotification() {
  return (
    <FormCard
      title="Send Push Notification"
      description="Send a push notification to church members"
      submitLabel="Send Notification"
      fields={[
        { name: "target", label: "Target Audience", type: "select", required: true, options: [
          { value: "all", label: "All Members" },
          { value: "sub-parish", label: "Sub Parish" },
          { value: "community", label: "Community" },
          { value: "group", label: "Group" },
        ]},
        { name: "title", label: "Notification Title", type: "text", placeholder: "Enter notification title", required: true },
        { name: "message", label: "Message", type: "textarea", placeholder: "Enter notification message", required: true, colSpan: 2 },
      ]}
    />
  );
}
