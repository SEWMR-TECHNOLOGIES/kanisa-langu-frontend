import DataTable from "../../components/head-parish/DataTable";
import { mockEvents } from "../../data/headParishMockData";

export default function ChurchEvents() {
  return (
    <DataTable
      title="Church Events"
      description="View all church events"
      columns={[
        { key: "title", label: "Event Title" },
        { key: "date", label: "Date" },
        { key: "location", label: "Location" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${r.status === "Upcoming" ? "bg-admin-info/10 text-admin-info" : "bg-admin-success/10 text-admin-success"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockEvents}
      searchPlaceholder="Search events..."
      searchKeys={["title"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
