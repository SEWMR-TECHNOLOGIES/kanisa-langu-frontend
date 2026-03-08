import TabbedFormCard from "../../components/head-parish/TabbedFormCard";

const sundayServiceOptions = [
  { value: "1", label: "Jan 5, 2025 - Epiphany Sunday" },
  { value: "2", label: "Jan 12, 2025 - Baptism of the Lord" },
  { value: "3", label: "Jan 19, 2025 - 2nd Sunday after Epiphany" },
];
const serviceNumberOptions = [
  { value: "1", label: "Service 1" }, { value: "2", label: "Service 2" }, { value: "3", label: "Service 3" },
];
const colorOptions = [
  { value: "Green", label: "Green" }, { value: "White", label: "White" },
  { value: "Purple", label: "Purple" }, { value: "Red", label: "Red" }, { value: "Black", label: "Black" },
];
const pageOptions = Array.from({ length: 50 }, (_, i) => ({ value: String(i + 1), label: `Page ${i + 1}` }));
const bookOptions = [
  { value: "1", label: "Mwanzo (Genesis)" }, { value: "2", label: "Kutoka (Exodus)" },
  { value: "40", label: "Mathayo (Matthew)" }, { value: "41", label: "Marko (Mark)" },
  { value: "42", label: "Luka (Luke)" }, { value: "43", label: "Yohana (John)" },
];
const chapterOptions = Array.from({ length: 28 }, (_, i) => ({ value: String(i + 1), label: `Chapter ${i + 1}` }));
const verseOptions = Array.from({ length: 50 }, (_, i) => ({ value: String(i + 1), label: `Verse ${i + 1}` }));
const songOptions = [
  { value: "1", label: "Bwana ni Mchungaji Wangu" }, { value: "2", label: "Mungu ni Pendo" },
  { value: "3", label: "Yesu Nakupenda" }, { value: "4", label: "Tukuza Jina Lake" },
];
const revenueStreamOptions = [
  { value: "1", label: "Sadaka ya Ibada" }, { value: "2", label: "Zaka" }, { value: "3", label: "Sadaka Maalum" },
];
const choirOptions = [
  { value: "1", label: "Kwaya Kuu" }, { value: "2", label: "Kwaya ya Vijana" }, { value: "3", label: "Kwaya ya Watoto" },
];
const leaderOptions = [
  { value: "1", label: "Mchg. John Mushi" }, { value: "2", label: "Mchg. Maria Kimaro" },
];
const elderOptions = [
  { value: "1", label: "Mzee Peter Urassa" }, { value: "2", label: "Mzee Grace Massawe" },
];

export default function RecordSundayService() {
  return (
    <TabbedFormCard
      title="Sunday Service Details"
      description="Record and manage Sunday service information"
      tabs={[
        { id: "main", label: "Main", submitLabel: "Save Service", fields: [
          { name: "service_date", label: "Service Date", type: "date", required: true },
          { name: "service_color", label: "Service Color", type: "select", required: true, options: colorOptions },
          { name: "small_liturgy_page_number", label: "Small Liturgy Page", type: "select", options: pageOptions },
          { name: "large_liturgy_page_number", label: "Large Liturgy Page", type: "select", options: pageOptions },
          { name: "small_antiphony_page_number", label: "Small Antiphony Page", type: "select", options: pageOptions },
          { name: "large_antiphony_page_number", label: "Large Antiphony Page", type: "select", options: pageOptions },
          { name: "small_praise_page_number", label: "Small Praise Books", type: "select", options: pageOptions },
          { name: "large_praise_page_number", label: "Large Praise Books", type: "select", options: pageOptions },
          { name: "base_scripture_text", label: "Base Scripture (Neno Kuu)", type: "textarea", placeholder: "Enter base scripture", colSpan: 2 },
        ]},
        { id: "service-times", label: "Service Times", submitLabel: "Set Service Time", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "service_number", label: "Service Number", type: "select", required: true, options: serviceNumberOptions },
          { name: "service_time", label: "Set Service Time", type: "time" },
        ]},
        { id: "scriptures", label: "Scriptures", submitLabel: "Save Scripture", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "book_id", label: "Select Book", type: "select", required: true, options: bookOptions },
          { name: "chapter", label: "Chapter Number", type: "select", required: true, options: chapterOptions },
          { name: "starting_verse_number", label: "Starting Verse", type: "select", required: true, options: verseOptions },
          { name: "ending_verse_number", label: "Ending Verse", type: "select", options: verseOptions },
        ]},
        { id: "songs", label: "Songs", submitLabel: "Add Song", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "song_id", label: "Select Song", type: "select", required: true, options: songOptions },
        ]},
        { id: "offerings", label: "Offerings", submitLabel: "Add Offering", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
        ]},
        { id: "leader", label: "Leader", submitLabel: "Assign Leader", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "service_number", label: "Service Number", type: "select", required: true, options: serviceNumberOptions },
          { name: "leader_id", label: "Select Leader", type: "select", required: true, options: leaderOptions },
        ]},
        { id: "preacher", label: "Preacher", submitLabel: "Assign Preacher", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "service_number", label: "Service Number", type: "select", required: true, options: serviceNumberOptions },
          { name: "preacher_id", label: "Select Preacher", type: "select", required: true, options: leaderOptions },
        ]},
        { id: "elders", label: "Elders", submitLabel: "Assign Elder", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "service_number", label: "Service Number", type: "select", required: true, options: serviceNumberOptions },
          { name: "elder_id", label: "Select Elder", type: "select", required: true, options: elderOptions },
        ]},
        { id: "choirs", label: "Choirs", submitLabel: "Add Choir", fields: [
          { name: "service_id", label: "Select Sunday Service", type: "select", required: true, options: sundayServiceOptions },
          { name: "service_number", label: "Service Number", type: "select", required: true, options: serviceNumberOptions },
          { name: "choir_id", label: "Select Choir", type: "select", required: true, options: choirOptions },
        ]},
      ]}
    />
  );
}
