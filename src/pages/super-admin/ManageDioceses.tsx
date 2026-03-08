import DataTable from "../../components/head-parish/DataTable";

const mockDioceses = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  diocese_name: ["Kilimanjaro", "Arusha", "Manyara", "Tanga", "Dodoma", "Morogoro", "Iringa", "Mbeya", "Kagera", "Mara", "Mwanza", "Shinyanga"][i],
  region: ["Kilimanjaro", "Arusha", "Manyara", "Tanga", "Dodoma", "Morogoro", "Iringa", "Mbeya", "Kagera", "Mara", "Mwanza", "Shinyanga"][i],
  district: ["Moshi Urban", "Arusha City", "Babati", "Tanga City", "Dodoma City", "Morogoro Urban", "Iringa Urban", "Mbeya City", "Bukoba", "Musoma", "Ilemela", "Shinyanga Urban"][i],
  email: `diocese${i + 1}@elct.or.tz`,
  phone: `+255 ${700 + i} 000 ${100 + i}`,
}));

export default function ManageDioceses() {
  return (
    <DataTable
      title="Manage Dioceses"
      description="View and manage all registered dioceses"
      columns={[
        { key: "diocese_name", label: "Diocese Name" },
        { key: "region", label: "Region" },
        { key: "district", label: "District" },
        { key: "email", label: "Email" },
        { key: "phone", label: "Phone" },
      ]}
      data={mockDioceses}
      searchPlaceholder="Search dioceses..."
      searchKeys={["diocese_name", "region"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
