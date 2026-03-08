// @ts-nocheck
export const mockCommunityMembers = Array.from({ length: 25 }, (_, i) => ({
  id: i + 1,
  name: `${["Juma", "Maria", "Peter", "Grace", "John", "Anna", "David", "Sarah", "James", "Ruth"][i % 10]} ${["Mwangi", "Kimaro", "Mushi", "Urassa", "Massawe", "Lyimo", "Shirima", "Pallangyo", "Maro", "Swai"][i % 10]}`,
  household: `Familia ya ${["Mwanga", "Amani", "Upendo", "Imani", "Baraka"][i % 5]}`,
  phone: `07${String(10000000 + i * 222).slice(0, 8)}`,
  status: i % 6 === 0 ? "Inactive" : "Active",
}));

export const mockHouseholds = Array.from({ length: 15 }, (_, i) => ({
  id: i + 1,
  name: `Familia ya ${["Mwanga", "Amani", "Upendo", "Imani", "Baraka", "Neema", "Furaha", "Tumaini", "Rehema", "Fadhili", "Heshima", "Uzima", "Wema", "Kazi", "Haki"][i]}`,
  head: `${["Juma", "Maria", "Peter", "Grace", "John"][i % 5]} ${["Mwangi", "Kimaro", "Mushi", "Urassa", "Massawe"][i % 5]}`,
  members_count: [5, 3, 7, 4, 6, 2, 5, 3, 8, 4, 3, 6, 5, 2, 4][i],
  location: ["Block A", "Block B", "Block C"][i % 3],
}));

export const mockCommunityMeetings = Array.from({ length: 6 }, (_, i) => ({
  id: i + 1,
  title: ["Community Prayer", "Fellowship Meeting", "Youth Outreach", "Women's Group", "Bible Study", "Planning Session"][i],
  date: `2025-0${1 + i}-${String(8 + i * 4).padStart(2, "0")}`,
  venue: ["Community Hall", "Elder's Home", "Church Grounds"][i % 3],
  attendees: [35, 20, 28, 22, 18, 15][i],
}));
