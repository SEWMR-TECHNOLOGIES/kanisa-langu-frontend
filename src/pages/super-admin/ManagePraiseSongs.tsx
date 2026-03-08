import DataTable from "../../components/head-parish/DataTable";

const mockSongs = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  song_number: `${i + 1}`,
  song_title: ["Bwana U Sifiwee", "Mungu Ni Mwema", "Yesu Ni Wangu", "Twaje Mbele", "Ni Nani Kama Wewe", "Mkono Wa Bwana", "Karibu Nyumbani", "Imani Ya Bwana", "Usiku Na Mchana", "Usiogope"][i],
}));

export default function ManagePraiseSongs() {
  return (
    <DataTable
      title="Manage Praise Songs"
      description="View and manage all registered praise songs"
      columns={[
        { key: "song_number", label: "#" },
        { key: "song_title", label: "Song Title" },
      ]}
      data={mockSongs}
      searchPlaceholder="Search songs..."
      searchKeys={["song_title", "song_number"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
