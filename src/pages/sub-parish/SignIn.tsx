import { Home } from "lucide-react";
import ElctSignIn from "../../components/elct/ElctSignIn";

export default function SubParishSignIn() {
  return (
    <ElctSignIn
      level="Sub Parish"
      icon={Home}
      accentFrom="hsl(280, 70%, 56%)"
      accentTo="hsl(270, 65%, 48%)"
      dashboardPath="/elct/sub-parish"
      contactLabel="Contact your Head Parish Admin"
      description="Handle day-to-day congregation activities, track member attendance, and manage local worship operations."
      stats={[
        { label: "Members", value: "485" },
        { label: "Communities", value: "4" },
        { label: "Weekly Attendance", value: "320" },
        { label: "Monthly Revenue", value: "TZS 2.8M" },
      ]}
    />
  );
}
