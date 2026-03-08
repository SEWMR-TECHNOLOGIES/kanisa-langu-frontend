// @ts-nocheck
export const mockHeadParishes = Array.from({ length: 7 }, (_, i) => ({
  id: i + 1,
  name: ["Msimbazi", "Azania", "Uhuru", "Kariakoo", "Buguruni", "Ilala", "Kijitonyama"][i],
  sub_parishes: [5, 3, 4, 6, 2, 4, 3][i],
  members: [2847, 1920, 2100, 3200, 1450, 1800, 2500][i],
  status: "Active",
}));

export const mockHPAdmins = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  name: `${["Rev.", "Mch.", "Bi."][i % 3]} ${["Amina Salum", "John Mwangi", "Grace Kimaro", "Peter Mushi", "Anna Urassa", "David Massawe", "Ruth Lyimo", "James Shirima", "Sarah Pallangyo", "Mark Maro"][i]}`,
  head_parish: mockHeadParishes[i % 7].name,
  email: `hp.admin${i + 1}@province.elct.or.tz`,
  status: i % 4 === 0 ? "Inactive" : "Active",
}));

export const mockProvinceOverview = {
  totalMembers: 12450,
  totalRevenue: "TZS 280,000,000",
  totalExpenses: "TZS 180,000,000",
  netIncome: "TZS 100,000,000",
};
