import TabbedFormCard from "../../components/head-parish/TabbedFormCard";
import { mockSubParishes } from "../../data/headParishMockData";

const bankOptions = mockBankAccounts.map(a => ({ value: String(a.id), label: `${a.account_name} - ${a.bank_name}` }));
const subParishOptions = mockSubParishes.map(s => ({ value: String(s.id), label: s.name || "" }));
const groupOptions = [
  { value: "1", label: "Vijana" }, { value: "2", label: "Wazee" },
  { value: "3", label: "Wanawake" }, { value: "4", label: "Kwaya Kuu" },
];
const revenueStreamOptions = [
  { value: "1", label: "Sadaka ya Ibada" }, { value: "2", label: "Zaka" },
  { value: "3", label: "Sadaka Maalum" }, { value: "4", label: "Ada ya Uanachama" },
];
const serviceNumberOptions = [
  { value: "1", label: "Service 1" }, { value: "2", label: "Service 2" }, { value: "3", label: "Service 3" },
];
const paymentMethodOptions = [
  { value: "Cash", label: "Cash" }, { value: "Bank Transfer", label: "Bank Transfer" },
  { value: "Mobile Payment", label: "Mobile Payment" }, { value: "Card", label: "Card" },
];
const communityOptions = [
  { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" },
  { value: "3", label: "Machame" }, { value: "4", label: "Kibosho" },
];

export default function RecordRevenue() {
  return (
    <TabbedFormCard
      title="Record Revenue"
      description="Record revenue at different management levels"
      infoBox="To post all recorded revenues to the bank, just click the <strong>Post to Bank</strong> button below the form."
      tabs={[
        { id: "head-parish", label: "Head Parish", submitLabel: "Record Revenue", fields: [
          { name: "service_number", label: "Service Number", type: "select", options: serviceNumberOptions },
          { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
          { name: "sub_parish_id", label: "Sub Parish", type: "select", options: subParishOptions },
          { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
          { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
          { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          { name: "description", label: "Description", type: "textarea", placeholder: "Enter description...", colSpan: 2 },
        ]},
        { id: "sub-parish", label: "Sub Parish", submitLabel: "Record Revenue", fields: [
          { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
          { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
          { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
          { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
          { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
        ]},
        { id: "community", label: "Community", submitLabel: "Record Revenue", fields: [
          { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
          { name: "community_id", label: "Community", type: "select", required: true, options: communityOptions },
          { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
          { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
          { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
          { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          { name: "description", label: "Description", type: "textarea", placeholder: "Enter description...", colSpan: 2 },
        ]},
        { id: "group", label: "Group", submitLabel: "Record Revenue", fields: [
          { name: "group_id", label: "Group", type: "select", required: true, options: groupOptions },
          { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
          { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
          { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
          { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
        ]},
        { id: "other", label: "Other HP Revenues", submitLabel: "Record Revenue", fields: [
          { name: "service_number", label: "Service Number", type: "select", options: serviceNumberOptions },
          { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
          { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
          { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
          { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
          { name: "description", label: "Description", type: "textarea", placeholder: "Enter description...", colSpan: 2 },
        ]},
      ]}
    />
  );
}
