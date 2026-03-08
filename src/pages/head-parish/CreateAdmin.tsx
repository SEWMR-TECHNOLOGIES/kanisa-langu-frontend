import TabbedFormCard from "../../components/head-parish/TabbedFormCard";
import { mockSubParishes } from "../../data/headParishMockData";

const subParishOptions = mockSubParishes.map(s => ({ value: String(s.id), label: s.name || "" }));
const communityOptions = [
  { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" },
  { value: "3", label: "Machame" }, { value: "4", label: "Kibosho" },
];
const groupOptions = [
  { value: "1", label: "Vijana" }, { value: "2", label: "Wazee" },
  { value: "3", label: "Wanawake" }, { value: "4", label: "Kwaya Kuu" },
];

const hpRoleOptions = [
  { value: "admin", label: "Admin" },
  { value: "secretary", label: "Secretary" },
  { value: "accountant", label: "Accountant" },
  { value: "clerk", label: "Clerk" },
  { value: "pastor", label: "Pastor" },
  { value: "evangelist", label: "Evangelist" },
];

const spRoleOptions = [
  { value: "secretary", label: "Secretary" },
  { value: "accountant", label: "Accountant" },
  { value: "chairperson", label: "Chairperson" },
];

const communityRoleOptions = [
  { value: "secretary", label: "Secretary" },
  { value: "accountant", label: "Accountant" },
  { value: "chairperson", label: "Chairperson" },
  { value: "elder", label: "Mzee wa Kanisa" },
];

const groupRoleOptions = [
  { value: "secretary", label: "Secretary" },
  { value: "accountant", label: "Accountant" },
  { value: "chairperson", label: "Chairperson" },
];

export default function CreateAdmin() {
  return (
    <TabbedFormCard
      title="Create System Users"
      description="Register new administrators at different management levels"
      tabs={[
        {
          id: "head-parish", label: "Head Parish", submitLabel: "Create Admin",
          fields: [
            { name: "admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
            { name: "admin_email", label: "Email", type: "email", placeholder: "Enter email", required: true },
            { name: "admin_phone", label: "Phone", type: "tel", placeholder: "Enter phone number", required: true },
            { name: "admin_role", label: "Role", type: "select", required: true, options: hpRoleOptions },
          ],
        },
        {
          id: "sub-parish", label: "Sub Parish", submitLabel: "Create Admin",
          fields: [
            { name: "admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
            { name: "admin_email", label: "Email", type: "email", placeholder: "Enter email", required: true },
            { name: "admin_phone", label: "Phone", type: "tel", placeholder: "Enter phone number", required: true },
            { name: "admin_role", label: "Role", type: "select", required: true, options: spRoleOptions },
            { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
          ],
        },
        {
          id: "community", label: "Community", submitLabel: "Create Admin",
          fields: [
            { name: "admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
            { name: "admin_email", label: "Email", type: "email", placeholder: "Enter email", required: true },
            { name: "admin_phone", label: "Phone", type: "tel", placeholder: "Enter phone number", required: true },
            { name: "admin_role", label: "Role", type: "select", required: true, options: communityRoleOptions },
            { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
            { name: "community_id", label: "Community", type: "select", required: true, options: communityOptions },
          ],
        },
        {
          id: "group", label: "Group", submitLabel: "Create Admin",
          fields: [
            { name: "admin_fullname", label: "Full Name", type: "text", placeholder: "Enter full name", required: true },
            { name: "admin_email", label: "Email", type: "email", placeholder: "Enter email", required: true },
            { name: "admin_phone", label: "Phone", type: "tel", placeholder: "Enter phone number", required: true },
            { name: "admin_role", label: "Role", type: "select", required: true, options: groupRoleOptions },
            { name: "group_id", label: "Group", type: "select", required: true, options: groupOptions },
          ],
        },
      ]}
    />
  );
}
