import FormCard from "../../components/head-parish/FormCard";

const typeOptions = [
  { value: "head-parish", label: "Head Parish" }, { value: "sub-parish", label: "Sub Parish" },
  { value: "community", label: "Community" }, { value: "groups", label: "Groups" },
];
const harambeeOptions = [
  { value: "1", label: "Church Building - 2025" }, { value: "2", label: "School Renovation - 2025" },
  { value: "3", label: "Pastor's House - 2024" },
];
const targetTypeOptions = [
  { value: "individual", label: "Individual" }, { value: "group", label: "Group" },
];

export default function UploadHarambeeTargets() {
  return (
    <FormCard
      title="Upload Harambee Targets"
      description="Bulk upload harambee target data from an Excel file"
      submitLabel="Upload Harambee Targets"
      infoBox={`<strong>Excel File Format:</strong><br/>
        <strong>For Individual Target Type:</strong> Column A: S/N, Column B: Church Member Name, Column C: Phone/Envelope Number, Column D: Target Amount<br/>
        <strong>For Group Target Type:</strong> Column A: S/N, Column B: Group Name, Column C: Phone/Envelope No. of Member 1, Column D: Phone/Envelope No. of Member 2, Column E: Group Target Amount<br/>
        <strong>Note:</strong> Ensure the Excel file follows the correct format and contains <u>only one header row</u>. If a phone number is used, it must start with <code>255</code> followed by exactly 9 digits.`}
      fields={[
        { name: "target", label: "Select Type", type: "select", required: true, options: typeOptions },
        { name: "harambee_id", label: "Harambee", type: "select", required: true, options: harambeeOptions },
        { name: "target_type", label: "Select Target Type", type: "select", required: true, options: targetTypeOptions },
        { name: "harambee_data", label: "Excel File", type: "file", accept: ".xls,.xlsx", required: true },
      ]}
    />
  );
}
