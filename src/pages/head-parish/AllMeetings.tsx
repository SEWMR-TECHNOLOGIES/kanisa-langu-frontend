import DataTable from "../../components/head-parish/DataTable";
import { mockMeetings } from "../../data/headParishMockData";

export default function AllMeetings() {
  return (
    <DataTable
      title="All Meetings"
      description="View and manage all scheduled meetings"
      columns={[
        { key: "title", label: "Meeting Title" },
        { key: "date", label: "Date" },
        { key: "venue", label: "Venue" },
        { key: "attendees", label: "Attendees" },
      ]}
      data={mockMeetings}
      searchPlaceholder="Search meetings..."
      searchKeys={["title", "venue"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
