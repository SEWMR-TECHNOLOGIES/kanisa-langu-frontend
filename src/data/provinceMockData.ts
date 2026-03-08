// @ts-nocheck
export const mockHeadParishes = Array.from({ length: 7 }, (_, i) => ({
  id: i + 1,
  name: ["Msimbazi", "Azania", "Uhuru", "Kariakoo", "Buguruni", "Ilala", "Kijitonyama"][i],
  province: "Northern Province",
  phone: `+255 7${i + 1}0000${i}00`,
  email: `parish${i + 1}@province.elct.or.tz`,
  address: ["P.O. Box 101", "P.O. Box 202", "P.O. Box 303", "P.O. Box 404", "P.O. Box 505", "P.O. Box 606", "P.O. Box 707"][i],
  sub_parishes: [5, 3, 4, 6, 2, 4, 3][i],
  members: [2847, 1920, 2100, 3200, 1450, 1800, 2500][i],
  status: "Active",
}));

export const mockHPAdmins = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  name: `${["Rev.", "Mch.", "Bi."][i % 3]} ${["Amina Salum", "John Mwangi", "Grace Kimaro", "Peter Mushi", "Anna Urassa", "David Massawe", "Ruth Lyimo", "James Shirima", "Sarah Pallangyo", "Mark Maro"][i]}`,
  head_parish: mockHeadParishes[i % 7].name,
  email: `hp.admin${i + 1}@province.elct.or.tz`,
  phone: `+255 7${i}${i}000${i}00`,
  role: ["admin", "pastor", "secretary", "chairperson", "admin", "pastor", "secretary", "admin", "pastor", "chairperson"][i],
  status: i % 4 === 0 ? "Inactive" : "Active",
}));

export const mockProvinceAdmins = Array.from({ length: 5 }, (_, i) => ({
  id: i + 1,
  name: `${["Rev.", "Mch.", "Bi.", "Evg.", "Mch."][i]} ${["Joseph Mwakagali", "Anna Kimaro", "Peter Mchome", "Grace Swai", "Ruth Massawe"][i]}`,
  email: `province.admin${i + 1}@province.elct.or.tz`,
  phone: `+255 7${i + 2}0000${i}00`,
  role: ["admin", "bishop", "secretary", "chairperson", "admin"][i],
  status: i === 3 ? "Inactive" : "Active",
}));

export const mockProvinceOverview = {
  totalMembers: 12450,
  totalRevenue: "TZS 280,000,000",
  totalExpenses: "TZS 180,000,000",
  netIncome: "TZS 100,000,000",
};
