import DataTable from "../../components/head-parish/DataTable";

const mockHP = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  name: ["Moshi Town", "Kibosho", "Marangu", "Old Moshi", "Mwika", "Machame", "Siha", "Masama", "Uru", "Kirua Vunjo"][i],
  province: ["Moshi", "Moshi", "Moshi", "Moshi", "Rombo", "Hai", "Siha", "Hai", "Moshi", "Rombo"][i],
  diocese: "Kilimanjaro",
}));

export default function ManageHeadParishes() {
  return (
    <DataTable
      title="Manage Head Parishes"
      description="View and manage all registered head parishes"
      columns={[
        { key: "name", label: "Head Parish Name" },
        { key: "province", label: "Province" },
        { key: "diocese", label: "Diocese" },
      ]}
      data={mockHP}
      searchPlaceholder="Search head parishes..."
      searchKeys={["name", "province"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
