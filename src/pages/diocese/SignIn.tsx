import { Building2 } from "lucide-react";
import ElctSignIn from "../../components/elct/ElctSignIn";

export default function DioceseSignIn() {
  return (
    <ElctSignIn
      level="Diocese"
      icon={Building2}
      accentFrom="hsl(210, 80%, 56%)"
      accentTo="hsl(220, 72%, 50%)"
      dashboardPath="/elct/diocese"
      contactLabel="Contact ELCT Headquarters"
      description="Oversee all provinces and parishes under your diocese with full financial visibility and administrative control."
      stats={[
        { label: "Provinces", value: "6" },
        { label: "Head Parishes", value: "42" },
        { label: "Total Members", value: "58,320" },
        { label: "Annual Revenue", value: "TZS 1.2B" },
      ]}
    />
  );
}
