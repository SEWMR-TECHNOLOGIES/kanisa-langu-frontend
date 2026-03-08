import { useAuth } from "@/contexts/AuthContext";

export default function Dashboard() {
  const { user } = useAuth();
  const now = new Date();
  const formatted = now.toLocaleDateString("en-GB", {
    weekday: "long", day: "2-digit", month: "short", year: "numeric",
    hour: "2-digit", minute: "2-digit",
  });

  return (
    <div className="bg-card rounded-xl border border-border p-6">
      <h2 className="text-xl font-bold text-foreground">
        Welcome, {user?.username}!
      </h2>
      <p className="text-muted-foreground mt-2">Current time: {formatted}</p>
    </div>
  );
}
