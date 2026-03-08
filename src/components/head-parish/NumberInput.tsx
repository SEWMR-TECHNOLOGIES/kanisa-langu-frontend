import { useState, useCallback } from "react";

interface NumberInputProps {
  name?: string;
  value?: string;
  onChange?: (rawValue: string) => void;
  placeholder?: string;
  required?: boolean;
  readOnly?: boolean;
  className?: string;
}

function formatNumber(val: string): string {
  const num = val.replace(/[^0-9.]/g, "");
  const parts = num.split(".");
  parts[0] = (parts[0] || "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.length > 1 ? `${parts[0]}.${parts[1]}` : parts[0];
}

function unformat(val: string): string {
  return val.replace(/,/g, "");
}

export default function NumberInput({
  name,
  value,
  onChange,
  placeholder,
  required,
  readOnly,
  className = "",
}: NumberInputProps) {
  const [display, setDisplay] = useState(() => value ? formatNumber(value) : "");

  const handleChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = unformat(e.target.value);
    const formatted = formatNumber(raw);
    setDisplay(formatted);
    onChange?.(raw);
  }, [onChange]);

  return (
    <>
      <input type="hidden" name={name} value={unformat(display)} />
      <input
        type="text"
        inputMode="numeric"
        value={display}
        onChange={handleChange}
        placeholder={placeholder}
        required={required}
        readOnly={readOnly}
        className={`admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all tabular-nums ${readOnly ? "opacity-60 cursor-not-allowed" : ""} ${className}`}
      />
    </>
  );
}
