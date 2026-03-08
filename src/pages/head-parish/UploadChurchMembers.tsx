import FormCard from "../../components/head-parish/FormCard";
import { mockSubParishes } from "../../data/headParishMockData";

const subParishOptions = mockSubParishes.map(s => ({ value: String(s.id), label: s.name || "" }));
const communityOptions = [
  { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" },
  { value: "3", label: "Machame" }, { value: "4", label: "Kibosho" },
];

export default function UploadChurchMembers() {
  return (
    <FormCard
      title="Upload Church Member Data"
      description="Bulk upload church members from an Excel file"
      submitLabel="Upload Members Details"
      infoBox={`<strong>Excel File Format:</strong><br/>
        <strong>Column A:</strong> S/N &bull; <strong>Column B:</strong> Full Name &bull; <strong>Column C:</strong> Envelope Number &bull; <strong>Column D:</strong> Phone Number<br/>
        <strong>Note:</strong> Please ensure the Excel file has <u>only one header row</u> at the top. All member data should begin from the second row.`}
      fields={[
        { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
        { name: "community_id", label: "Community", type: "select", required: true, options: communityOptions },
        { name: "member_data", label: "Excel File", type: "file", accept: ".xls,.xlsx", required: true, colSpan: 2 },
      ]}
    />
  );
}
