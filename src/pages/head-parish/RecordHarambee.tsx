import TabbedFormCard from "../../components/head-parish/TabbedFormCard";
import { mockSubParishes, mockBankAccounts } from "../../data/headParishMockData";

const bankOptions = mockBankAccounts.map(a => ({ value: String(a.id), label: `${a.account_name} - ${a.bank_name}` }));
const subParishOptions = mockSubParishes.map(s => ({ value: String(s.id), label: s.name || "" }));
const communityOptions = [
  { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" },
  { value: "3", label: "Machame" }, { value: "4", label: "Kibosho" },
];
const groupOptions = [
  { value: "1", label: "Vijana" }, { value: "2", label: "Wazee" },
  { value: "3", label: "Wanawake" }, { value: "4", label: "Kwaya Kuu" },
];

export default function RecordHarambee() {
  return (
    <TabbedFormCard
      title="Add New Harambee"
      description="Create a new harambee fundraiser at different levels"
      tabs={[
        {
          id: "head-parish",
          label: "Head Parish",
          submitLabel: "Submit Harambee",
          fields: [
            { name: "name", label: "Name", type: "text", placeholder: "Enter harambee name", required: true },
            { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
            { name: "account_id", label: "Bank Account", type: "select", required: true, options: bankOptions },
            { name: "amount", label: "Amount", type: "number", placeholder: "Amount" },
            { name: "from_date", label: "From Date", type: "date", required: true },
            { name: "to_date", label: "To Date", type: "date", required: true },
          ],
        },
        {
          id: "sub-parish",
          label: "Sub Parish",
          submitLabel: "Submit Harambee",
          fields: [
            { name: "name", label: "Name", type: "text", placeholder: "Enter harambee name", required: true },
            { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
            { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
            { name: "account_id", label: "Bank Account", type: "select", required: true, options: bankOptions },
            { name: "amount", label: "Amount", type: "number", placeholder: "Amount" },
            { name: "from_date", label: "From Date", type: "date", required: true },
            { name: "to_date", label: "To Date", type: "date", required: true },
          ],
        },
        {
          id: "community",
          label: "Community",
          submitLabel: "Submit Harambee",
          fields: [
            { name: "name", label: "Name", type: "text", placeholder: "Enter harambee name", required: true },
            { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
            { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
            { name: "community_id", label: "Community", type: "select", required: true, options: communityOptions },
            { name: "account_id", label: "Bank Account", type: "select", required: true, options: bankOptions },
            { name: "amount", label: "Amount", type: "number", placeholder: "Amount" },
            { name: "from_date", label: "From Date", type: "date", required: true },
            { name: "to_date", label: "To Date", type: "date", required: true },
          ],
        },
        {
          id: "group",
          label: "Group",
          submitLabel: "Submit Harambee",
          fields: [
            { name: "name", label: "Name", type: "text", placeholder: "Enter harambee name", required: true },
            { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
            { name: "group_id", label: "Group", type: "select", required: true, options: groupOptions },
            { name: "account_id", label: "Bank Account", type: "select", required: true, options: bankOptions },
            { name: "amount", label: "Amount", type: "number", placeholder: "Amount" },
            { name: "from_date", label: "From Date", type: "date", required: true },
            { name: "to_date", label: "To Date", type: "date", required: true },
          ],
        },
      ]}
    />
  );
}
