// @ts-nocheck
// Mock data for all head parish admin pages

export const mockMembers = Array.from({ length: 45 }, (_, i) => ({
  id: i + 1,
  title: ["Mch.", "Bi.", "Ndg.", "Dkt."][i % 4],
  first_name: ["Juma", "Maria", "Peter", "Grace", "John", "Anna", "David", "Sarah", "James", "Ruth"][i % 10],
  middle_name: ["A.", "B.", "C.", "K.", "M.", "N.", "P.", "S.", "T.", "W."][i % 10],
  last_name: ["Mwangi", "Kimaro", "Mushi", "Urassa", "Massawe", "Lyimo", "Shirima", "Pallangyo", "Maro", "Swai"][i % 10],
  date_of_birth: `${10 + (i % 20)}-0${1 + (i % 9)}-${1960 + (i % 40)}`,
  phone: `0${7 + (i % 3)}${String(10000000 + i * 111).slice(0, 8)}`,
  occupation: ["Teacher", "Farmer", "Doctor", "Engineer", "Nurse", "Business", "Student", "Pastor", "Driver", "Clerk"][i % 10],
  sub_parish: ["Moshi Mjini", "Moshi Vijijini", "Hai", "Rombo"][i % 4],
  community: ["Mwika", "Marangu", "Machame", "Kibosho", "Siha"][i % 5],
  type: i % 3 === 0 ? "Mgeni" : "Mwenyeji",
  envelope_number: `Y${String(i + 1).padStart(3, "0")}`,
}));

export const mockSubParishes = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  name: ["Moshi Mjini", "Moshi Vijijini", "Hai", "Rombo", "Siha", "Same", "Mwanga", "Lushoto"][i],
  description: `Sub parish serving the ${["Moshi Mjini", "Moshi Vijijini", "Hai", "Rombo", "Siha", "Same", "Mwanga", "Lushoto"][i]} area`,
}));

export const mockCommunities = Array.from({ length: 15 }, (_, i) => ({
  id: i + 1,
  name: ["Mwika", "Marangu", "Machame", "Kibosho", "Siha", "Uru", "Kirua", "Mamba", "Shira", "Old Moshi", "Kilema", "Masama", "Keni", "Mriti", "Arusha Chini"][i],
  sub_parish: mockSubParishes[i % 4].name,
  description: `Community in ${mockSubParishes[i % 4].name} sub parish`,
}));

export const mockGroups = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  name: ["Vijana", "Wazee", "Wanawake", "Kwaya Kuu", "Usharika", "MWIKA", "Umoja", "Upendo", "Amani", "Imani"][i],
  description: `Church group for ${["Youth", "Elders", "Women", "Main choir", "Parish council", "MWIKA group", "Unity group", "Love group", "Peace group", "Faith group"][i]}`,
}));

export const mockLeaders = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  name: `${["Rev.", "Mch.", "Bi.", "Evg."][i % 4]} ${["John Mwangi", "Grace Kimaro", "Peter Mushi", "Anna Urassa", "David Massawe", "Ruth Lyimo"][i % 6]}`,
  role: ["Parish Pastor", "Secretary", "Accountant", "Evangelist", "Elder", "Deacon"][i % 6],
  appointment_date: `01-0${1 + (i % 9)}-202${i % 4}`,
  end_date: i % 3 === 0 ? `31-12-2025` : "",
  status: i % 4 === 0 ? "Inactive" : "Active",
}));

export const mockChoirs = Array.from({ length: 6 }, (_, i) => ({
  id: i + 1,
  name: ["Kwaya Kuu", "Kwaya ya Vijana", "Kwaya ya Watoto", "Kwaya ya Wanawake", "Kwaya ya Injili", "Kwaya ya Krismasi"][i],
  members_count: [45, 32, 28, 38, 22, 15][i],
  leader: ["John Mwangi", "Grace Kimaro", "Peter Mushi", "Anna Urassa", "David Massawe", "Ruth Lyimo"][i],
}));

export const mockServices = Array.from({ length: 20 }, (_, i) => ({
  id: i + 1,
  service_date: `${2025}-${String(1 + (i % 12)).padStart(2, "0")}-${String(1 + (i * 7) % 28).padStart(2, "0")}`,
  scripture: ["Mathayo 5:1-12", "Yohana 3:16", "Warumi 8:28", "Zaburi 23:1-6", "Isaya 40:31"][i % 5],
  color: ["Green", "White", "Purple", "Red", "Blue"][i % 5],
}));

export const mockBankAccounts = Array.from({ length: 5 }, (_, i) => ({
  id: i + 1,
  account_name: ["Main Account", "Building Fund", "Harambee Account", "Missions Fund", "Emergency Fund"][i],
  bank_name: ["CRDB", "NMB", "NBC", "Stanbic", "Equity"][i],
  account_number: `${String(1000000000 + i * 1111111111).slice(0, 13)}`,
  balance: ["5,420,000", "12,800,000", "3,200,000", "1,500,000", "850,000"][i],
}));

export const mockRevenueStreams = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  name: ["Sadaka ya Ibada", "Zaka", "Sadaka Maalum", "Ada ya Uanachama", "Michango ya Harambee", "Mapato ya Nyumba", "Sadaka za Shukrani", "Fungu la Kumi", "Michango ya Vijana", "Sadaka ya Pasaka", "Sadaka ya Krismasi", "Mapato Mengine"][i],
  account_name: mockBankAccounts[i % 5].account_name,
}));

export const mockHarambee = {
  headParish: Array.from({ length: 5 }, (_, i) => ({
    id: i + 1,
    description: ["Church Building", "School Renovation", "Pastor's House", "New Roof", "Water Project"][i],
    from_date: "01-01-2025",
    to_date: "31-12-2025",
    amount: ["50,000,000", "25,000,000", "15,000,000", "8,000,000", "12,000,000"][i],
    account_name: mockBankAccounts[i % 5].account_name,
  })),
  subParish: Array.from({ length: 4 }, (_, i) => ({
    id: i + 1,
    description: ["Local Building Fund", "Youth Center", "Road Repair", "Clinic Support"][i],
    from_date: "01-01-2025",
    to_date: "30-06-2025",
    amount: ["10,000,000", "5,000,000", "3,000,000", "7,000,000"][i],
    sub_parish_name: mockSubParishes[i].name,
    account_name: mockBankAccounts[i % 5].account_name,
  })),
  community: Array.from({ length: 3 }, (_, i) => ({
    id: i + 1,
    description: ["Community Hall", "Farm Equipment", "Borehole"][i],
    from_date: "01-03-2025",
    to_date: "31-08-2025",
    amount: ["5,000,000", "2,500,000", "4,000,000"][i],
    community_name: mockCommunities[i].name,
    sub_parish_name: mockSubParishes[i].name,
    account_name: mockBankAccounts[i % 5].account_name,
  })),
  group: Array.from({ length: 3 }, (_, i) => ({
    id: i + 1,
    description: ["Youth Trip", "Women Conference", "Elder Retreat"][i],
    from_date: "01-06-2025",
    to_date: "30-09-2025",
    amount: ["2,000,000", "3,500,000", "1,500,000"][i],
    group_name: mockGroups[i].name,
    account_name: mockBankAccounts[i % 5].account_name,
  })),
};

export const mockMeetings = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  title: ["Parish Council", "Finance Committee", "Youth Meeting", "Women Fellowship", "Elders Meeting", "Choir Practice", "Planning Session", "Budget Review"][i],
  date: `2025-0${1 + (i % 9)}-${String(5 + i * 3).padStart(2, "0")}`,
  venue: ["Church Hall", "Conference Room", "Parish Office"][i % 3],
  attendees: [25, 12, 35, 28, 15, 40, 18, 10][i],
}));

export const mockEvents = Array.from({ length: 6 }, (_, i) => ({
  id: i + 1,
  title: ["Easter Celebration", "Christmas Service", "Youth Camp", "Women's Day", "Harvest Festival", "New Year Prayer"][i],
  date: `2025-0${1 + i * 2}-${String(10 + i).padStart(2, "0")}`,
  location: ["Main Church", "Church Grounds", "Camp Site"][i % 3],
  status: ["Upcoming", "Completed", "Upcoming", "Completed", "Upcoming", "Upcoming"][i],
}));

export const mockExpenseRequests = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  description: ["Office Supplies", "Fuel", "Printing", "Electricity", "Water Bill", "Internet", "Repairs", "Equipment", "Travel", "Food"][i],
  amount: `${(i + 1) * 150000}`,
  requested_by: mockMembers[i].first_name + " " + mockMembers[i].last_name,
  date: `2025-0${1 + (i % 9)}-${String(1 + i * 3).padStart(2, "0")}`,
  status: ["Pending", "Approved", "Rejected", "Pending", "Approved"][i % 5],
}));

export const mockDebits = Array.from({ length: 7 }, (_, i) => ({
  id: i + 1,
  description: ["Building Material Loan", "Vehicle Loan", "Equipment Finance", "Land Purchase", "Renovation Loan", "Emergency Fund", "School Fees"][i],
  amount: `${(i + 1) * 2000000}`,
  creditor: ["CRDB Bank", "NMB Bank", "Individual Donor", "Diocese", "NBC Bank"][i % 5],
  date: `2025-0${1 + (i % 9)}-01`,
  status: ["Active", "Paid", "Active", "Active", "Paid", "Active", "Active"][i],
}));

export const mockExcludedMembers = Array.from({ length: 5 }, (_, i) => ({
  id: i + 1,
  name: `${mockMembers[i + 20].first_name} ${mockMembers[i + 20].last_name}`,
  reason: ["Relocated", "Deceased", "Transferred", "Inactive", "Personal Request"][i],
  date: `2025-0${1 + i}-15`,
  excluded_by: "Admin",
}));

export const mockPaymentWallets = Array.from({ length: 3 }, (_, i) => ({
  id: i + 1,
  name: ["M-Pesa", "Tigo Pesa", "Airtel Money"][i],
  wallet_number: `0${7 + i}${String(10000000 + i * 1234567).slice(0, 8)}`,
  status: "Active",
}));

export const mockAssets = Array.from({ length: 6 }, (_, i) => ({
  id: i + 1,
  name: ["Church Building", "Parish House", "School Block", "Guest House", "Farm Land", "Vehicle"][i],
  status: ["Active", "Active", "Under Repair", "Active", "Active", "Inactive"][i],
  value: ["500,000,000", "150,000,000", "200,000,000", "80,000,000", "120,000,000", "45,000,000"][i],
}));

export const mockEnvelopes = Array.from({ length: 15 }, (_, i) => ({
  id: i + 1,
  member_name: `${mockMembers[i].first_name} ${mockMembers[i].last_name}`,
  envelope_number: `Y${String(i + 1).padStart(3, "0")}`,
  target: `${(i + 1) * 50000}`,
  contributed: `${(i + 1) * 30000}`,
  balance: `${(i + 1) * 20000}`,
}));
