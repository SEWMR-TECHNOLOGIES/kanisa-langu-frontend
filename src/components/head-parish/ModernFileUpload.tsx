import { useState, useRef } from "react";
import { Upload, X, FileSpreadsheet, File } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

interface ModernFileUploadProps {
  name: string;
  accept?: string;
  required?: boolean;
  label?: string;
  onChange?: (file: File | null) => void;
}

export default function ModernFileUpload({
  name,
  accept = ".xls,.xlsx",
  required,
  label,
  onChange,
}: ModernFileUploadProps) {
  const [file, setFile] = useState<File | null>(null);
  const [dragOver, setDragOver] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleFile = (f: File | null) => {
    setFile(f);
    onChange?.(f);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setDragOver(false);
    const f = e.dataTransfer.files?.[0];
    if (f) {
      handleFile(f);
      if (inputRef.current) {
        const dt = new DataTransfer();
        dt.items.add(f);
        inputRef.current.files = dt.files;
      }
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    handleFile(e.target.files?.[0] || null);
  };

  const removeFile = () => {
    handleFile(null);
    if (inputRef.current) inputRef.current.value = "";
  };

  const isExcel = file?.name.match(/\.(xls|xlsx|csv)$/i);

  return (
    <div>
      <input
        ref={inputRef}
        type="file"
        name={name}
        accept={accept}
        required={required && !file}
        onChange={handleChange}
        className="sr-only"
        id={`file-${name}`}
      />

      <AnimatePresence mode="wait">
        {!file ? (
          <motion.label
            key="dropzone"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            htmlFor={`file-${name}`}
            onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
            onDragLeave={() => setDragOver(false)}
            onDrop={handleDrop}
            className={`flex flex-col items-center justify-center gap-3 px-6 py-8 rounded-xl border-2 border-dashed cursor-pointer transition-all duration-200 ${
              dragOver
                ? "border-admin-accent bg-admin-accent/5 scale-[1.01]"
                : "border-admin-border/40 hover:border-admin-accent/50 hover:bg-admin-surface-hover/50"
            }`}
          >
            <div className={`w-12 h-12 rounded-xl flex items-center justify-center transition-colors ${
              dragOver ? "bg-admin-accent/15 text-admin-accent" : "bg-admin-surface-hover text-admin-text/50"
            }`}>
              <Upload className="w-5 h-5" />
            </div>
            <div className="text-center">
              <p className="text-sm font-medium text-admin-text-bright">
                {label || "Drop file here or click to browse"}
              </p>
              <p className="text-xs text-admin-text/50 mt-1">
                {accept.replace(/\./g, "").toUpperCase().replace(/,/g, ", ")} files supported
              </p>
            </div>
          </motion.label>
        ) : (
          <motion.div
            key="preview"
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.95 }}
            className="flex items-center gap-3 px-4 py-3 rounded-xl bg-admin-accent/5 border border-admin-accent/20"
          >
            <div className="w-10 h-10 rounded-lg bg-admin-accent/10 flex items-center justify-center flex-shrink-0">
              {isExcel ? (
                <FileSpreadsheet className="w-5 h-5 text-admin-success" />
              ) : (
                <File className="w-5 h-5 text-admin-accent" />
              )}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium text-admin-text-bright truncate">{file.name}</p>
              <p className="text-xs text-admin-text/50">{(file.size / 1024).toFixed(1)} KB</p>
            </div>
            <button
              type="button"
              onClick={removeFile}
              className="p-1.5 rounded-lg hover:bg-destructive/10 text-admin-text/50 hover:text-destructive transition-colors flex-shrink-0"
            >
              <X className="w-4 h-4" />
            </button>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
