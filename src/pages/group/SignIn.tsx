import { Users } from "lucide-react";
import ElctSignIn from "../../components/elct/ElctSignIn";

export default function GroupSignIn() {
  return (
    <ElctSignIn
      level="Group"
      icon={Users}
      accentFrom="hsl(280, 65%, 55%)"
      accentTo="hsl(270, 60%, 48%)"
      dashboardPath="/elct/group"
      contactLabel="Contact your Head Parish Admin"
      description="Manage your church group members, contributions, harambee targets, and financial records."
      stats={[
        { label: "Members", value: "45" },
        { label: "Harambee", value: "67%" },
        { label: "Revenue", value: "TZS 2.4M" },
        { label: "Envelopes", value: "38" },
      ]}
    />
  );
}
