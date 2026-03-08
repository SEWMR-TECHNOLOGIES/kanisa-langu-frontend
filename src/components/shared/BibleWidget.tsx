import { useState, useMemo } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { ChevronLeft, ChevronRight, Quote } from "lucide-react";
import bibleIcon from "@/assets/bible-icon.png";

const DAILY_VERSES = [
  { ref: "Zaburi 23:1", sw: "Bwana ndiye mchungaji wangu; sitapungukiwa na kitu.", en: "The LORD is my shepherd; I shall not want." },
  { ref: "Yeremia 29:11", sw: "Maana nayajua mawazo ninayowawazia, asema Bwana; ni mawazo ya amani wala si ya mabaya.", en: "For I know the plans I have for you, declares the LORD, plans for welfare and not for evil." },
  { ref: "Filipi 4:13", sw: "Nayaweza mambo yote katika yeye anitiaye nguvu.", en: "I can do all things through him who strengthens me." },
  { ref: "Isaya 40:31", sw: "Bali wao wamngojeao Bwana watapata nguvu mpya.", en: "But they who wait for the LORD shall renew their strength." },
  { ref: "Zaburi 46:10", sw: "Tulieni, mjue ya kuwa Mimi ndimi Mungu.", en: "Be still, and know that I am God." },
  { ref: "Yohana 3:16", sw: "Kwa maana Mungu aliupenda ulimwengu hivi, hata akamtoa Mwanawe wa pekee.", en: "For God so loved the world, that he gave his only Son." },
  { ref: "Mithali 3:5-6", sw: "Mtumainie Bwana kwa moyo wako wote, wala usizitegemee akili zako mwenyewe.", en: "Trust in the LORD with all your heart, and do not lean on your own understanding." },
  { ref: "Warumi 8:28", sw: "Nasi twajua ya kuwa katika mambo yote Mungu hufanya kazi pamoja na wale wampendao.", en: "And we know that for those who love God all things work together for good." },
  { ref: "Mathayo 11:28", sw: "Njoni kwangu, ninyi nyote msumbukao na wenye kulemewa, nami nitawapumzisha.", en: "Come to me, all who labor and are heavy laden, and I will give you rest." },
  { ref: "Zaburi 119:105", sw: "Neno lako ni taa ya miguu yangu, na mwanga wa njia yangu.", en: "Your word is a lamp to my feet and a light to my path." },
  { ref: "Yoshua 1:9", sw: "Uwe hodari na kushujaa; usiogope wala usifadhaike.", en: "Be strong and courageous. Do not be frightened, and do not be dismayed." },
  { ref: "Isaya 41:10", sw: "Usiogope, kwa maana mimi ni pamoja nawe.", en: "Fear not, for I am with you; be not dismayed, for I am your God." },
  { ref: "2 Timotheo 1:7", sw: "Maana Mungu hakutupa roho ya woga, bali ya nguvu na ya upendo.", en: "For God gave us a spirit not of fear but of power and love and self-control." },
  { ref: "Zaburi 37:4", sw: "Jifurahishe katika Bwana, naye atakupa haja za moyo wako.", en: "Delight yourself in the LORD, and he will give you the desires of your heart." },
  { ref: "Wagalatia 5:22-23", sw: "Lakini tunda la Roho ni upendo, furaha, amani, uvumilivu, utu wema, fadhili.", en: "But the fruit of the Spirit is love, joy, peace, patience, kindness, goodness." },
  { ref: "1 Yohana 4:19", sw: "Sisi twapenda kwa maana yeye alitupenda sisi kwanza.", en: "We love because he first loved us." },
  { ref: "Zaburi 150:6", sw: "Kila mwenye pumzi na amsifu Bwana. Msifuni Bwana.", en: "Let everything that has breath praise the LORD! Praise the LORD!" },
  { ref: "Waebrania 11:1", sw: "Basi, imani ni kuwa na hakika ya mambo yatarajiwayo.", en: "Now faith is the assurance of things hoped for, the conviction of things not seen." },
  { ref: "Marko 11:24", sw: "Yote myaombayo mkisali, aminini ya kwamba mmeyapokea.", en: "Whatever you ask in prayer, believe that you have received it, and it will be yours." },
  { ref: "Zaburi 91:1-2", sw: "Aketiye mahali pa siri pa Aliye Juu, atakaa katika uvuli wake Mwenyezi.", en: "He who dwells in the shelter of the Most High will abide in the shadow of the Almighty." },
  { ref: "Kumbukumbu 31:6", sw: "Iweni hodari, mwe na moyo mkuu, msiogope, wala msifadhaike.", en: "Be strong and courageous. Do not fear or be in dread of them." },
  { ref: "Warumi 12:12", sw: "Furahini kwa tumaini; sabiri katika dhiki; dumuni katika kuomba.", en: "Rejoice in hope, be patient in tribulation, be constant in prayer." },
  { ref: "1 Wakorintho 16:14", sw: "Mambo yenu yote na yafanyike katika upendo.", en: "Let all that you do be done in love." },
  { ref: "Waefeso 2:8-9", sw: "Kwa maana mmeokolewa kwa neema, kwa njia ya imani.", en: "For by grace you have been saved through faith." },
  { ref: "Zaburi 139:14", sw: "Nakushukuru kwa kuwa nimeumbwa kwa jinsi ya ajabu.", en: "I praise you, for I am fearfully and wonderfully made." },
  { ref: "Mathayo 6:33", sw: "Bali utafuteni kwanza ufalme wake Mungu, na haki yake.", en: "But seek first the kingdom of God and his righteousness." },
  { ref: "Isaya 55:11", sw: "Ndivyo litakavyokuwa neno langu litokalo kinywani mwangu; halitanirudia bure.", en: "So shall my word be that goes out from my mouth; it shall not return to me empty." },
  { ref: "Yohana 14:27", sw: "Amani nawaachieni; amani yangu nawapa.", en: "Peace I leave with you; my peace I give to you." },
  { ref: "Zaburi 34:8", sw: "Onjeni mwone ya kuwa Bwana ni mwema.", en: "Oh, taste and see that the LORD is good!" },
  { ref: "2 Wakorintho 5:17", sw: "Hata imekuwa mtu akiwa ndani ya Kristo amekuwa kiumbe kipya.", en: "Therefore, if anyone is in Christ, he is a new creation." },
  { ref: "Zaburi 118:24", sw: "Siku hii ndiyo siku aliyoifanya Bwana; na tufurahi, tushangilie.", en: "This is the day that the LORD has made; let us rejoice and be glad in it." },
];

interface BibleWidgetProps {
  className?: string;
}

export default function BibleWidget({ className = "" }: BibleWidgetProps) {
  const dayOfYear = useMemo(() => {
    const now = new Date();
    const start = new Date(now.getFullYear(), 0, 0);
    return Math.floor((now.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));
  }, []);

  const [index, setIndex] = useState(dayOfYear % DAILY_VERSES.length);
  const [lang, setLang] = useState<"sw" | "en">("sw");

  const verse = DAILY_VERSES[index] ?? DAILY_VERSES[0]!;

  const prev = () => setIndex((i) => (i - 1 + DAILY_VERSES.length) % DAILY_VERSES.length);
  const next = () => setIndex((i) => (i + 1) % DAILY_VERSES.length);

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: 0.6 }}
      className={`relative rounded-2xl overflow-hidden ${className}`}
    >
      {/* Rich background with cross pattern */}
      <div className="absolute inset-0 bg-gradient-to-br from-[hsl(32,40%,12%)] via-[hsl(28,35%,10%)] to-[hsl(24,30%,8%)]" />
      <div className="absolute inset-0 opacity-[0.03]" style={{
        backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M28 0v24h-24v4h24v24h4v-24h24v-4h-24v-24z' fill='%23d4a574' fill-opacity='1'/%3E%3C/svg%3E")`
      }} />
      {/* Warm glow */}
      <div className="absolute top-0 right-0 w-40 h-40 bg-[hsl(38,70%,50%)] rounded-full blur-[100px] opacity-[0.08]" />
      <div className="absolute bottom-0 left-0 w-32 h-32 bg-[hsl(20,60%,40%)] rounded-full blur-[80px] opacity-[0.06]" />

      <div className="relative p-6 space-y-5">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            {/* Book icon styled like a real bible */}
            <img src={bibleIcon} alt="Bible" className="w-10 h-10" />
            <div>
              <h3 className="text-sm font-bold text-[hsl(38,50%,80%)] tracking-wide">Biblia Takatifu</h3>
              <p className="text-[10px] text-[hsl(30,20%,50%)] font-medium">Neno la Leo · Daily Verse</p>
            </div>
          </div>

          {/* Language toggle */}
          <div className="flex items-center bg-[hsl(25,20%,15%)] rounded-lg p-0.5 border border-[hsl(30,20%,18%)]">
            {(["sw", "en"] as const).map((l) => (
              <button
                key={l}
                onClick={() => setLang(l)}
                className={`px-3 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all ${
                  lang === l
                    ? "bg-gradient-to-r from-[hsl(38,60%,45%)] to-[hsl(28,55%,40%)] text-[hsl(38,30%,95%)] shadow-sm"
                    : "text-[hsl(30,20%,45%)] hover:text-[hsl(30,30%,60%)]"
                }`}
              >
                {l === "sw" ? "Kiswahili" : "English"}
              </button>
            ))}
          </div>
        </div>

        {/* Decorative divider */}
        <div className="flex items-center gap-3">
          <div className="flex-1 h-px bg-gradient-to-r from-transparent via-[hsl(38,40%,30%)] to-transparent opacity-40" />
          <div className="w-1.5 h-1.5 rounded-full bg-[hsl(38,50%,45%)] opacity-50" />
          <div className="flex-1 h-px bg-gradient-to-r from-transparent via-[hsl(38,40%,30%)] to-transparent opacity-40" />
        </div>

        {/* Verse content */}
        <AnimatePresence mode="wait">
          <motion.div
            key={`${index}-${lang}`}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -8 }}
            transition={{ duration: 0.25 }}
            className="space-y-4 py-2"
          >
            {/* Quote mark and reference */}
            <div className="flex items-start gap-3">
              <Quote className="w-5 h-5 text-[hsl(38,50%,45%)] opacity-40 mt-0.5 flex-shrink-0 rotate-180" />
              <div className="space-y-3 flex-1">
                <p className="text-[15px] leading-[1.8] text-[hsl(35,30%,78%)] font-serif italic">
                  {lang === "sw" ? verse.sw : verse.en}
                </p>
                <div className="flex items-center gap-2">
                  <div className="w-6 h-px bg-[hsl(38,50%,45%)] opacity-40" />
                  <span className="text-xs font-bold text-[hsl(38,55%,55%)] tracking-wide">{verse.ref}</span>
                </div>
              </div>
            </div>
          </motion.div>
        </AnimatePresence>

        {/* Decorative divider */}
        <div className="flex items-center gap-3">
          <div className="flex-1 h-px bg-gradient-to-r from-transparent via-[hsl(38,40%,30%)] to-transparent opacity-40" />
          <div className="w-1.5 h-1.5 rounded-full bg-[hsl(38,50%,45%)] opacity-50" />
          <div className="flex-1 h-px bg-gradient-to-r from-transparent via-[hsl(38,40%,30%)] to-transparent opacity-40" />
        </div>

        {/* Navigation */}
        <div className="flex items-center justify-between">
          <button
            onClick={prev}
            className="w-8 h-8 rounded-lg bg-[hsl(25,20%,15%)] border border-[hsl(30,20%,20%)] hover:border-[hsl(38,40%,35%)] flex items-center justify-center transition-all group"
          >
            <ChevronLeft className="w-4 h-4 text-[hsl(30,20%,45%)] group-hover:text-[hsl(38,50%,60%)] transition-colors" />
          </button>

          <div className="flex items-center gap-1.5">
            {Array.from({ length: 5 }).map((_, i) => {
              const dotIndex = (index - 2 + i + DAILY_VERSES.length) % DAILY_VERSES.length;
              return (
                <div
                  key={i}
                  className={`rounded-full transition-all duration-300 ${
                    dotIndex === index
                      ? "w-5 h-1.5 bg-gradient-to-r from-[hsl(38,60%,50%)] to-[hsl(28,55%,40%)]"
                      : "w-1.5 h-1.5 bg-[hsl(30,20%,25%)]"
                  }`}
                />
              );
            })}
          </div>

          <button
            onClick={next}
            className="w-8 h-8 rounded-lg bg-[hsl(25,20%,15%)] border border-[hsl(30,20%,20%)] hover:border-[hsl(38,40%,35%)] flex items-center justify-center transition-all group"
          >
            <ChevronRight className="w-4 h-4 text-[hsl(30,20%,45%)] group-hover:text-[hsl(38,50%,60%)] transition-colors" />
          </button>
        </div>
      </div>
    </motion.div>
  );
}
