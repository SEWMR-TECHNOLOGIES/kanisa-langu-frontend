import { Construction } from "lucide-react";

interface Props {
  title: string;
  description?: string;
}

export default function PagePlaceholder({ title, description }: Props) {
  return (
    <div className="bg-card rounded-xl border border-border p-8 text-center">
      <Construction className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
      <h1 className="text-2xl font-bold text-foreground">{title}</h1>
      <p className="text-muted-foreground mt-2">
        {description || "This page is under construction and will be implemented from the legacy PHP version."}
      </p>
    </div>
  );
}
