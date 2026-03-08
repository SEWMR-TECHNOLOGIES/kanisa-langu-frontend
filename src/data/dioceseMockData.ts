// @ts-nocheck
export const mockProvinces = Array.from({ length: 6 }, (_, i) => ({
  id: i + 1,
  name: ["Northern Province", "Eastern Province", "Southern Province", "Western Province", "Central Province", "Lake Province"][i],
  diocese: "Diocese of Moshi",
  region: ["Kilimanjaro", "Tanga", "Iringa", "Mbeya", "Dodoma", "Mwanza"][i],
  district: ["Moshi", "Korogwe", "Iringa Mjini", "Mbeya Mjini", "Dodoma Mjini", "Ilemela"][i],
  phone: [`+255 ${700 + i}000000`][0] || "",
  email: `province${i + 1}@diocese.elct.or.tz`,
  address: ["P.O. Box 100, Moshi", "P.O. Box 200, Korogwe", "P.O. Box 300, Iringa", "P.O. Box 400, Mbeya", "P.O. Box 500, Dodoma", "P.O. Box 600, Mwanza"][i],
  head_parishes: [7, 5, 8, 4, 6, 9][i],
  total_members: [12450, 8900, 15200, 6700, 9800, 11300][i],
  status: "Active",
}));

export const mockProvinceAdmins = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  name: `${["Rev.", "Mch.", "Bi.", "Evg."][i % 4]} ${["John Mwamba", "Grace Lema", "Peter Ndosi", "Anna Swai", "David Kimaro", "Ruth Mfinanga", "James Urassa", "Sarah Mushi"][i]}`,
  province: mockProvinces[i % 6].name,
  email: `admin${i + 1}@diocese.elct.or.tz`,
  phone: `+255 7${i}${i}000${i}00`,
  role: ["admin", "bishop", "secretary", "chairperson", "admin", "secretary", "admin", "bishop"][i],
  status: i % 5 === 0 ? "Inactive" : "Active",
}));

export const mockDioceseAdmins = Array.from({ length: 4 }, (_, i) => ({
  id: i + 1,
  name: `${["Bishop", "Rev.", "Mch.", "Bi."][i]} ${["Michael Mtawa", "Grace Mushi", "John Lyimo", "Anna Pallangyo"][i]}`,
  role: ["Bishop", "General Secretary", "Treasurer", "Administrator"][i],
  email: `${["bishop", "secretary", "treasurer", "admin"][i]}@diocese.elct.or.tz`,
  phone: `+255 7${i + 1}0000000`,
  diocese: "Diocese of Moshi",
  status: "Active",
}));
