import DataTable from "../../components/head-parish/DataTable";

const mockAdmins = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  fullname: ["John Mwakasege", "Sarah Kimaro", "Peter Lyimo", "Mary Shirima", "Joseph Moshi", "Anna Minja", "David Temba", "Grace Urassa"][i],
  email: `admin${i + 1}@elct.or.tz`,
  phone: `+255 7${i}0 000 ${100 + i}`,
  diocese: ["Kilimanjaro", "Arusha", "Manyara", "Tanga", "Dodoma", "Morogoro", "Iringa", "Mbeya"][i],
  role: ["Admin", "Bishop", "Secretary", "Admin", "Chairperson", "Admin", "Secretary", "Bishop"][i],
}));

export default function DioceseAdminsList() {
  return (
    <DataTable
      title="Diocese Admins List"
      description="View and manage diocese administrators"
      columns={[
        { key: "fullname", label: "Full Name" },
        { key: "email", label: "Email" },
        { key: "phone", label: "Phone" },
        { key: "diocese", label: "Diocese" },
        { key: "role", label: "Role", render: (r: any) => (
          <span className="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-admin-accent/10 text-admin-accent">
            {r.role}
          </span>
        )},
      ]}
      data={mockAdmins}
      searchPlaceholder="Search admins..."
      searchKeys={["fullname", "diocese"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
