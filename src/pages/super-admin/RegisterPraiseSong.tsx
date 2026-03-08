import FormCard from "../../components/head-parish/FormCard";

export default function RegisterPraiseSong() {
  return (
    <FormCard
      title="Register Praise Song"
      description="Add a new praise song to the system"
      submitLabel="Register Song"
      fields={[
        { name: "song_title", label: "Song Title", type: "text", placeholder: "Enter Song Title", required: true },
        { name: "song_number", label: "Song Number", type: "text", placeholder: "Enter Song Number" },
        { name: "song_lyrics", label: "Lyrics", type: "textarea", placeholder: "Enter song lyrics...", colSpan: 2 },
      ]}
    />
  );
}
