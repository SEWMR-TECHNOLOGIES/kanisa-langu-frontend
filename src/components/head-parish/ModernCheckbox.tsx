import { Check } from "lucide-react";

interface ModernCheckboxProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  label?: string;
  className?: string;
}

export default function ModernCheckbox({ checked, onChange, label, className = "" }: ModernCheckboxProps) {
  return (
    <label className={`relative flex items-center gap-2.5 cursor-pointer select-none group ${className}`}>
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
        className="sr-only peer"
      />
      <div className="w-[18px] h-[18px] rounded-md border-2 border-admin-border peer-checked:border-admin-accent peer-checked:bg-admin-accent transition-all duration-200 flex items-center justify-center group-hover:border-admin-accent/60 shrink-0">
        {checked && <Check className="w-3 h-3 text-white" strokeWidth={3} />}
      </div>
      {label && <span className="text-sm text-admin-text group-hover:text-admin-text-bright transition-colors">{label}</span>}
    </label>
  );
}
