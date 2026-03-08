import { Church } from "lucide-react";
import ElctSignIn from "../../components/elct/ElctSignIn";

export default function SignIn() {
  return (
    <ElctSignIn
      level="Head Parish"
      icon={Church}
      accentFrom="hsl(42, 92%, 56%)"
      accentTo="hsl(32, 90%, 48%)"
      dashboardPath="/elct/head-parish"
      contactLabel="Contact your Diocese Admin"
      description="Manage your parish, track contributions, oversee finances, and lead your church community with confidence."
      stats={[
        { label: "Members", value: "2,847" },
        { label: "Revenue Tracked", value: "TZS 15.2M" },
        { label: "Sub Parishes", value: "8" },
        { label: "Communities", value: "15" },
      ]}
    />
  );
}
