import DataTable from "../../components/head-parish/DataTable";

const mockDistricts = [
  { id: 1, name: "Moshi Urban", region: "Kilimanjaro" },
  { id: 2, name: "Moshi Rural", region: "Kilimanjaro" },
  { id: 3, name: "Hai", region: "Kilimanjaro" },
  { id: 4, name: "Rombo", region: "Kilimanjaro" },
  { id: 5, name: "Arusha City", region: "Arusha" },
  { id: 6, name: "Meru", region: "Arusha" },
  { id: 7, name: "Babati", region: "Manyara" },
  { id: 8, name: "Tanga City", region: "Tanga" },
];

export default function ManageDistricts() {
  return (
    <DataTable
      title="Manage Districts"
      description="View and manage all registered districts"
      columns={[
        { key: "name", label: "District Name" },
        { key: "region", label: "Region" },
      ]}
      data={mockDistricts}
      searchPlaceholder="Search districts..."
      searchKeys={["name", "region"]}
      actions={["edit", "delete"]}
    />
  );
}
