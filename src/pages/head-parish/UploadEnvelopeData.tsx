import FormCard from "../../components/head-parish/FormCard";

export default function UploadEnvelopeData() {
  return (
    <FormCard
      title="Upload Envelope Data"
      description="Bulk upload envelope contribution data from an Excel file"
      submitLabel="Upload Data"
      infoBox={`<strong>Excel File Format:</strong><br/>
        <strong>Column A:</strong> Envelope Number &bull; <strong>Column B:</strong> Amount Paid &bull; <strong>Column C (optional):</strong> Payment Method (Cash, Card, Mobile Payment, Bank Transfer)<br/>
        <strong>Note:</strong> If Payment Method is left blank, it defaults to "Cash". Ensure the file follows this order.`}
      fields={[
        { name: "date", label: "Date", type: "date", required: true },
        { name: "harambee_data", label: "Excel File", type: "file", accept: ".xls,.xlsx", required: true },
      ]}
    />
  );
}
