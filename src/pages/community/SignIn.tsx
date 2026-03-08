import { UsersRound } from "lucide-react";
import ElctSignIn from "../../components/elct/ElctSignIn";

export default function CommunitySignIn() {
  return (
    <ElctSignIn
      level="Community"
      icon={UsersRound}
      accentFrom="hsl(350, 75%, 55%)"
      accentTo="hsl(340, 70%, 48%)"
      dashboardPath="/elct/community"
      contactLabel="Contact your Sub Parish Admin"
      description="Manage neighborhood fellowship groups, care networks, and grassroots engagement within your community."
      stats={[
        { label: "Households", value: "68" },
        { label: "Members", value: "124" },
        { label: "Meetings/Month", value: "8" },
        { label: "Contributions", value: "TZS 450K" },
      ]}
    />
  );
}
