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

export default function VerifyRevenues() {
  return (
    <TabbedFormCard
      title="Verify Collected Revenues"
      description="Select a date to verify revenues at each management level"
      tabs={[
        {
          id: "head-parish",
          label: "Head Parish",
          submitLabel: "Verify Head Parish Revenues",
          fields: [
            { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          ],
        },
        {
          id: "sub-parish",
          label: "Sub Parish",
          submitLabel: "Verify Sub Parish Revenues",
          fields: [
            { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
            { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          ],
        },
        {
          id: "community",
          label: "Community",
          submitLabel: "Verify Community Revenues",
          fields: [
            { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
            { name: "community_id", label: "Community", type: "select", required: true, options: communityOptions },
            { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          ],
        },
        {
          id: "group",
          label: "Group",
          submitLabel: "Verify Group Revenues",
          fields: [
            { name: "group_id", label: "Group", type: "select", required: true, options: groupOptions },
            { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          ],
        },
      ]}
    />
  );
}
