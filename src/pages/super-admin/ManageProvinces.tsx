import DataTable from "../../components/head-parish/DataTable";

const mockProvinces = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  province_name: ["Moshi", "Hai", "Rombo", "Same", "Mwanga", "Siha", "Arusha Urban", "Meru", "Monduli", "Babati"][i],
  diocese: ["Kilimanjaro", "Kilimanjaro", "Kilimanjaro", "Kilimanjaro", "Kilimanjaro", "Kilimanjaro", "Arusha", "Arusha", "Arusha", "Manyara"][i],
}));

export default function ManageProvinces() {
  return (
    <DataTable
      title="Manage Provinces"
      description="View and manage all registered provinces"
      columns={[
        { key: "province_name", label: "Province Name" },
        { key: "diocese", label: "Diocese" },
      ]}
      data={mockProvinces}
      searchPlaceholder="Search provinces..."
      searchKeys={["province_name", "diocese"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
