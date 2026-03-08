import { MapPin } from "lucide-react";
import ElctSignIn from "../../components/elct/ElctSignIn";

export default function ProvinceSignIn() {
  return (
    <ElctSignIn
      level="Province"
      icon={MapPin}
      accentFrom="hsl(160, 70%, 45%)"
      accentTo="hsl(170, 65%, 38%)"
      dashboardPath="/elct/province"
      contactLabel="Contact your Diocese Admin"
      description="Coordinate head parishes across your province, manage regional reporting and oversee operational activities."
      stats={[
        { label: "Head Parishes", value: "7" },
        { label: "Sub Parishes", value: "34" },
        { label: "Members", value: "12,450" },
        { label: "Revenue", value: "TZS 280M" },
      ]}
    />
  );
}
