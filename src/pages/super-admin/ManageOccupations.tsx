import DataTable from "../../components/head-parish/DataTable";

const mockOccupations = [
  "Teacher", "Doctor", "Engineer", "Farmer", "Business", "Pastor", "Nurse", "Lawyer", "Student", "Retired"
].map((name, i) => ({ id: i + 1, occupation_name: name }));

export default function ManageOccupations() {
  return (
    <DataTable
      title="Manage Occupations"
      description="View and manage all registered occupations"
      columns={[{ key: "occupation_name", label: "Occupation" }]}
      data={mockOccupations}
      searchPlaceholder="Search occupations..."
      searchKeys={["occupation_name"]}
      actions={["edit", "delete"]}
    />
  );
}
