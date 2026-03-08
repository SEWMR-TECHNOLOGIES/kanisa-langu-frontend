import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "head_parish", label: "Head Parish" },
  { key: "admins", label: "Province Admins" },
];

const data = Array.from({ length: 5 }, (_, i) => ({
  id: i + 1,
  head_parish: `Admin ${i + 1}`,
  admins: `admin${i + 1}@province.org`,
}));

export default function ProvinceAdminsList() {
  return (
    <DataTable
      title="Province Admins"
      description="All province administrators"
      columns={columns}
      data={data}
      searchPlaceholder="Search admins..."
      searchKeys={["head_parish"]}
      actions={["view", "edit"]}
    />
  );
}
