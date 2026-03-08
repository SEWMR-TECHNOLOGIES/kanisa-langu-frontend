import ElctSignIn from "../../components/elct/ElctSignIn";
import { Users } from "lucide-react";

export default function GroupSignIn() {
  return (
    <ElctSignIn
      levelLabel="Group Admin"
      levelIcon={Users}
      dashboardPath="/elct/group"
      breadcrumbs={[
        { label: "ELCT", href: "/" },
        { label: "Group" },
      ]}
    />
  );
}
