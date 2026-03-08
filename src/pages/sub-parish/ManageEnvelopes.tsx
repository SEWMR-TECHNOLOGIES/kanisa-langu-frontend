import { useState } from "react";
import { motion } from "framer-motion";
import { Printer } from "lucide-react";
import DataTable from "../../components/head-parish/DataTable";
import { mockSPEnvelopes } from "../../data/subParishMockData";

const yearTabs = ["2026", "2025", "2024"];

export default function SPManageEnvelopes() {
  const [activeYear, setActiveYear] = useState(yearTabs[0]);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Manage Envelopes</h1>
        <p className="text-sm text-admin-text mt-1">View and manage member envelope contributions by year</p>
      </div>

      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        className="admin-card rounded-2xl overflow-hidden"
      >
        <div className="px-6 pt-6 border-b border-admin-border/30">
          <div className="flex gap-1 overflow-x-auto pt-2 pb-0 scrollbar-none">
            {yearTabs.map((year) => (
              <button
                key={year}
                onClick={() => setActiveYear(year)}
                className={`relative px-4 py-2.5 text-sm font-medium whitespace-nowrap rounded-t-xl transition-all duration-200 ${
                  activeYear === year
                    ? "text-admin-accent bg-admin-accent/5 border-b-2 border-admin-accent"
                    : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                }`}
              >
                {year}
              </button>
            ))}
          </div>
        </div>

        <div className="p-0">
          <DataTable
            title=""
            columns={[
              { key: "member", label: "Member" },
              { key: "envelope_number", label: "Envelope #" },
              { key: "target", label: "Target" },
              { key: "contributed", label: "Contributed" },
              { key: "balance", label: "Balance" },
            ]}
            data={mockSPEnvelopes}
            searchPlaceholder="Search envelopes..."
            searchKeys={["member", "envelope_number"]}
            actions={["delete"]}
            customActions={[
              {
                label: "Print Statement",
                icon: Printer,
                className: "text-admin-info hover:bg-admin-info/10",
                onClick: (row: any) => {
                  window.open(`#print-statement/${row.id}/${activeYear}`, "_blank");
                },
              },
            ]}
          />
        </div>
      </motion.div>
    </div>
  );
}
