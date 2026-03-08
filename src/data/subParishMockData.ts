// @ts-nocheck
export const mockSPMembers = Array.from({ length: 30 }, (_, i) => ({
  id: i + 1,
  name: `${["Juma", "Maria", "Peter", "Grace", "John", "Anna"][i % 6]} ${["Mwangi", "Kimaro", "Mushi", "Urassa", "Massawe", "Lyimo"][i % 6]}`,
  community: ["Mwika", "Marangu", "Machame", "Kibosho"][i % 4],
  phone: `07${String(10000000 + i * 111).slice(0, 8)}`,
  envelope: `Y${String(i + 1).padStart(3, "0")}`,
  status: i % 5 === 0 ? "Inactive" : "Active",
}));

export const mockSPServices = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  date: `2025-${String(1 + (i % 12)).padStart(2, "0")}-${String(1 + (i * 7) % 28).padStart(2, "0")}`,
  attendance: [280, 320, 310, 295, 340, 350, 290, 330, 315, 300, 345, 360][i],
  offering: `TZS ${[450, 520, 380, 490, 560, 600, 410, 530, 470, 510, 580, 620][i]},000`,
}));

export const mockSPRevenueStreams = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  name: ["Sadaka ya Ibada", "Zaka", "Sadaka Maalum", "Ada ya Uanachama", "Michango", "Mapato ya Nyumba", "Shukrani", "Fungu la Kumi"][i],
  account: ["Main Account", "Building Fund", "Main Account", "Main Account"][i % 4],
}));

export const mockSPMeetings = Array.from({ length: 6 }, (_, i) => ({
  id: i + 1,
  title: ["Parish Council", "Finance Committee", "Youth Meeting", "Women Fellowship", "Elders Meeting", "Prayer Meeting"][i],
  date: `2025-0${1 + i}-${String(5 + i * 3).padStart(2, "0")}`,
  venue: ["Church Hall", "Conference Room", "Parish Office"][i % 3],
  attendees: [25, 12, 35, 28, 15, 40][i],
}));

export const mockSPHarambee = Array.from({ length: 4 }, (_, i) => ({
  id: i + 1,
  description: ["Church Building", "Youth Center", "Road Repair", "Clinic Support"][i],
  target: `TZS ${[10, 5, 3, 7][i]},000,000`,
  collected: `TZS ${[6, 3, 1.5, 4][i]},000,000`,
  progress: [60, 60, 50, 57][i],
}));

export const mockSPEnvelopes = Array.from({ length: 15 }, (_, i) => ({
  id: i + 1,
  member: `${["Juma", "Maria", "Peter", "Grace", "John"][i % 5]} ${["Mwangi", "Kimaro", "Mushi", "Urassa", "Massawe"][i % 5]}`,
  envelope_number: `Y${String(i + 1).padStart(3, "0")}`,
  target: `TZS ${(i + 1) * 50},000`,
  contributed: `TZS ${(i + 1) * 30},000`,
  balance: `TZS ${(i + 1) * 20},000`,
}));

export const mockSPExpenseRequests = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  description: ["Office Supplies", "Fuel", "Printing", "Electricity", "Water Bill", "Internet", "Repairs", "Equipment"][i],
  amount: `TZS ${(i + 1) * 150},000`,
  requested_by: mockSPMembers[i].name,
  date: `2025-0${1 + (i % 9)}-${String(1 + i * 3).padStart(2, "0")}`,
  status: ["Pending", "Approved", "Rejected", "Pending", "Approved"][i % 5],
}));
