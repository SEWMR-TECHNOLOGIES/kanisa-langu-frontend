import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "name", label: "Head Parish" },
  { key: "province", label: "Province" },
  { key: "members", label: "Members" },
  { key: "status", label: "Status", render: (row: any) => (
    <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${row.status === "Active" ? "bg-admin-success/10 text-admin-success" : "bg-admin-text/10 text-admin-text"}`}>{row.status}</span>
  )},
];

const data = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  name: `Usharika wa ${["Moshi", "Hai", "Rombo", "Same", "Mwanga", "Siha", "Arusha", "Meru", "Karatu", "Monduli", "Babati", "Hanang"][i]}`,
  province: ["Kilimanjaro", "Kilimanjaro", "Kilimanjaro", "Pare", "Pare", "Kilimanjaro", "Arusha", "Arusha", "Arusha", "Arusha", "Manyara", "Manyara"][i],
  members: [450, 380, 290, 210, 180, 150, 520, 340, 280, 190, 310, 250][i],
  status: i % 4 === 3 ? "Inactive" : "Active",
}));

export default function DioceseManageHeadParishes() {
  return (
    <DataTable
      title="Manage Head Parishes"
      description="All registered head parishes in the diocese"
      columns={columns}
      data={data}
      searchPlaceholder="Search head parishes..."
      searchKeys={["name", "province"]}
      actions={["view", "edit"]}
    />
  );
}
