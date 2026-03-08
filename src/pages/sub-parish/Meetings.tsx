import DataTable from "../../components/head-parish/DataTable";
import { mockSPMeetings } from "../../data/subParishMockData";

export default function SPMeetings() {
  return (
    <DataTable
      title="Meetings"
      description="View all meetings"
      columns={[
        { key: "title", label: "Meeting Title" },
        { key: "date", label: "Date" },
        { key: "venue", label: "Venue" },
        { key: "attendees", label: "Attendees" },
      ]}
      data={mockSPMeetings}
      searchPlaceholder="Search meetings..."
      searchKeys={["title", "venue"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
