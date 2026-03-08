import { useState, useMemo } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { BookOpen, ChevronLeft, ChevronRight, Sparkles } from "lucide-react";

// Curated daily verses (Swahili + English) for church admin dashboards
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
  // Use day-of-year to determine "today's verse"
  const dayOfYear = useMemo(() => {
    const now = new Date();
    const start = new Date(now.getFullYear(), 0, 0);
    const diff = now.getTime() - start.getTime();
    return Math.floor(diff / (1000 * 60 * 60 * 24));
  }, []);

  const [index, setIndex] = useState(dayOfYear % DAILY_VERSES.length);
  const [lang, setLang] = useState<"sw" | "en">("sw");

  const verse = DAILY_VERSES[index];

  const prev = () => setIndex((i) => (i - 1 + DAILY_VERSES.length) % DAILY_VERSES.length);
  const next = () => setIndex((i) => (i + 1) % DAILY_VERSES.length);

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: 0.6 }}
      className={`admin-card rounded-2xl overflow-hidden ${className}`}
    >
      {/* Header gradient bar */}
      <div className="h-1 bg-gradient-to-r from-admin-accent via-admin-warning to-admin-accent" />

      <div className="p-5 space-y-4">
        {/* Title row */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-lg bg-admin-accent/10 flex items-center justify-center">
              <BookOpen className="w-4 h-4 text-admin-accent" />
            </div>
            <div>
              <h3 className="text-xs font-bold text-admin-text-bright uppercase tracking-wider">Neno la Leo</h3>
              <p className="text-[10px] text-admin-text/50">Daily Scripture</p>
            </div>
          </div>

          {/* Language toggle */}
          <div className="flex items-center bg-admin-surface-hover rounded-lg p-0.5">
            {(["sw", "en"] as const).map((l) => (
              <button
                key={l}
                onClick={() => setLang(l)}
                className={`px-2.5 py-1 rounded-md text-[10px] font-semibold uppercase transition-all ${
                  lang === l
                    ? "bg-admin-accent text-admin-bg shadow-sm"
                    : "text-admin-text/50 hover:text-admin-text"
                }`}
              >
                {l === "sw" ? "SW" : "EN"}
              </button>
            ))}
          </div>
        </div>

        {/* Verse content */}
        <AnimatePresence mode="wait">
          <motion.div
            key={`${index}-${lang}`}
            initial={{ opacity: 0, x: 10 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -10 }}
            transition={{ duration: 0.2 }}
            className="space-y-3"
          >
            {/* Scripture reference badge */}
            <div className="flex items-center gap-1.5">
              <Sparkles className="w-3 h-3 text-admin-accent" />
              <span className="text-xs font-bold text-admin-accent">{verse.ref}</span>
            </div>

            {/* Verse text */}
            <blockquote className="text-sm leading-relaxed text-admin-text-bright font-medium pl-3 border-l-2 border-admin-accent/30">
              &ldquo;{lang === "sw" ? verse.sw : verse.en}&rdquo;
            </blockquote>
          </motion.div>
        </AnimatePresence>

        {/* Navigation */}
        <div className="flex items-center justify-between pt-1">
          <button
            onClick={prev}
            className="w-7 h-7 rounded-lg bg-admin-surface-hover hover:bg-admin-accent/10 flex items-center justify-center transition-colors group"
          >
            <ChevronLeft className="w-3.5 h-3.5 text-admin-text group-hover:text-admin-accent transition-colors" />
          </button>

          <div className="flex items-center gap-1">
            {Array.from({ length: 5 }).map((_, i) => {
              const dotIndex = (index - 2 + i + DAILY_VERSES.length) % DAILY_VERSES.length;
              return (
                <div
                  key={i}
                  className={`rounded-full transition-all ${
                    dotIndex === index
                      ? "w-4 h-1.5 bg-admin-accent"
                      : "w-1.5 h-1.5 bg-admin-text/15"
                  }`}
                />
              );
            })}
          </div>

          <button
            onClick={next}
            className="w-7 h-7 rounded-lg bg-admin-surface-hover hover:bg-admin-accent/10 flex items-center justify-center transition-colors group"
          >
            <ChevronRight className="w-3.5 h-3.5 text-admin-text group-hover:text-admin-accent transition-colors" />
          </button>
        </div>
      </div>
    </motion.div>
  );
}
