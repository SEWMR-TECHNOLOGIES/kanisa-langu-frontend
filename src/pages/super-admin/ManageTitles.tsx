import DataTable from "../../components/head-parish/DataTable";

const mockTitles = [
  "Rev.", "Dr.", "Prof.", "Mr.", "Mrs.", "Ms.", "Dkt.", "Mch.", "Bw.", "Bi."
].map((name, i) => ({ id: i + 1, title_name: name }));

export default function ManageTitles() {
  return (
    <DataTable
      title="Manage Titles"
      description="View and manage all registered titles"
      columns={[{ key: "title_name", label: "Title" }]}
      data={mockTitles}
      searchPlaceholder="Search titles..."
      searchKeys={["title_name"]}
      actions={["edit", "delete"]}
    />
  );
}
