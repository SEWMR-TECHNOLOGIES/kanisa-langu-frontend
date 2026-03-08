import DataTable from "../../components/head-parish/DataTable";

const mockRegions = [
  "Kilimanjaro", "Arusha", "Manyara", "Tanga", "Dodoma", "Morogoro",
  "Iringa", "Mbeya", "Kagera", "Mara", "Mwanza", "Shinyanga", "Tabora", "Kigoma",
].map((name, i) => ({ id: i + 1, name }));

export default function ManageRegions() {
  return (
    <DataTable
      title="Manage Regions"
      description="View and manage all registered regions"
      columns={[{ key: "name", label: "Region Name" }]}
      data={mockRegions}
      searchPlaceholder="Search regions..."
      searchKeys={["name"]}
      actions={["edit", "delete"]}
    />
  );
}
