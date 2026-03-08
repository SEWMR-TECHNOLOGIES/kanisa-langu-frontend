import DataTable from "../../components/head-parish/DataTable";
import { mockCommunityMeetings } from "../../data/communityMockData";

export default function CommunityMeetings() {
  return (
    <DataTable
      title="Community Meetings"
      description="View and manage community meetings"
      columns={[
        { key: "title", label: "Meeting Title" },
        { key: "date", label: "Date" },
        { key: "venue", label: "Venue" },
        { key: "attendees", label: "Attendees" },
      ]}
      data={mockCommunityMeetings}
      searchPlaceholder="Search meetings..."
      searchKeys={["title", "venue"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
