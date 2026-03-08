import { useParams, Link } from "react-router-dom";
import { motion } from "framer-motion";
import { ArrowLeft, Check, TrendingUp, Users, CreditCard, BarChart3, Shield, Bell, Building2, MapPin, Church, Home, UsersRound, Sparkles } from "lucide-react";
import Navbar from "../components/landing/Navbar";
import Footer from "../components/landing/Footer";
import elctIcon from "../assets/elct-icon.png";
import rcIcon from "../assets/rc-icon.png";
import sdaIcon from "../assets/sda-icon.png";
import pentecostalIcon from "../assets/pentecostal-icon.png";
import appMockup from "../assets/app-mockup.png";
import rcIcon from "../assets/rc-icon.png";
import sdaIcon from "../assets/sda-icon.png";
import pentecostalIcon from "../assets/pentecostal-icon.png";

const elctLevels = [
  { icon: Building2, name: "Diocese", description: "Top-level administrative unit overseeing all provinces, parishes, and operations within a region." },
  { icon: MapPin, name: "Province", description: "A grouping of head parishes under the diocese, coordinating regional activities and reporting." },
  { icon: Church, name: "Head Parish", description: "The central parish responsible for managing sub-parishes and community outreach in its area." },
  { icon: Home, name: "Sub Parish", description: "A local worship center under a head parish, handling day-to-day congregation activities." },
  { icon: UsersRound, name: "Communities", description: "Neighborhood-level groups within a sub-parish for fellowship, care, and grassroots engagement." },
  { icon: Users, name: "Church Members", description: "Individual member records with full profiles, contributions, attendance, and sacramental history." },
];

const churchData: Record<string, {
  name: string;
  fullName: string;
  icon: string;
  accent: string;
  accentLight: string;
  description: string;
  tagline: string;
  features: { icon: typeof TrendingUp; title: string; desc: string }[];
  benefits: string[];
}> = {
  elct: {
    name: "ELCT",
    fullName: "Evangelical Lutheran Church in Tanzania",
    icon: elctIcon,
    accent: "hsl(220 72% 50%)",
    accentLight: "hsl(220 72% 50% / 0.08)",
    tagline: "Streamline Diocese and Parish Operations",
    description: "Kanisa Langu for ELCT provides comprehensive tools tailored to the Lutheran church structure, from Diocese management to individual parish tracking, offering full visibility into financial operations and member engagement across all levels.",
    features: [
      { icon: TrendingUp, title: "Diocese Revenue Tracking", desc: "Monitor income across all parishes under a diocese with real-time dashboards and aggregated reporting." },
      { icon: Users, title: "Parish Member Registry", desc: "Manage congregation records with baptism, confirmation, and membership tracking across all parishes." },
      { icon: CreditCard, title: "Offering and Tithe Collection", desc: "Digital collection system supporting M-Pesa, Tigo Pesa, and bank transfers for seamless giving." },
      { icon: BarChart3, title: "Multi-Level Reporting", desc: "Generate reports at parish, district, and diocese levels with customizable date ranges and categories." },
      { icon: Shield, title: "Role-Based Access", desc: "Assign roles to bishops, pastors, evangelists, and secretaries with granular permission controls." },
      { icon: Bell, title: "Congregation Notifications", desc: "Send targeted SMS and push notifications to specific parishes or the entire diocese." },
    ],
    benefits: [
      "Complete diocese-level financial oversight",
      "Hierarchical church structure support",
      "Automated monthly and annual reports",
      "Multi-parish management from one dashboard",
      "Secure data with role-based access control",
      "Integration with mobile money platforms",
    ],
  },
  "roman-catholic": {
    name: "Roman Catholic",
    fullName: "Roman Catholic Church",
    icon: rcIcon,
    accent: "hsl(0 72% 50%)",
    accentLight: "hsl(0 72% 50% / 0.08)",
    tagline: "Empower Parish and Diocese Administration",
    description: "Kanisa Langu for Roman Catholic churches provides purpose-built tools for managing parish operations, sacramental records, and diocesan oversight, from small parishes to large archdioceses.",
    features: [
      { icon: TrendingUp, title: "Parish Financial Management", desc: "Track all parish income streams including collections, donations, and special fundraising campaigns." },
      { icon: Users, title: "Sacramental Records", desc: "Maintain comprehensive records of baptisms, confirmations, marriages, and other sacraments." },
      { icon: CreditCard, title: "Donation Management", desc: "Facilitate regular and one-time donations through mobile money and card payments with receipts." },
      { icon: BarChart3, title: "Deanery and Diocese Reports", desc: "Consolidate financial and operational data across deaneries for diocese-level insights." },
      { icon: Shield, title: "Clergy and Staff Management", desc: "Manage roles for parish priests, deacons, catechists, and administrative staff." },
      { icon: Bell, title: "Parish Communications", desc: "Schedule masses, events, and send announcements to parishioners via SMS and notifications." },
    ],
    benefits: [
      "Sacramental record digitization",
      "Multi-parish diocese management",
      "Automated financial reconciliation",
      "Event and mass scheduling tools",
      "Secure hierarchical access controls",
      "Parishioner engagement analytics",
    ],
  },
  sda: {
    name: "SDA",
    fullName: "Seventh-Day Adventist Church",
    icon: sdaIcon,
    accent: "hsl(190 72% 40%)",
    accentLight: "hsl(190 72% 40% / 0.08)",
    tagline: "Strengthen Conference and Church Operations",
    description: "Kanisa Langu for SDA churches supports the unique organizational structure, from local churches to conferences and unions, with tools designed for Sabbath operations, tithe management, and member care.",
    features: [
      { icon: TrendingUp, title: "Tithe and Offering Management", desc: "Track tithes, offerings, and special funds with automatic allocation to conference and union levels." },
      { icon: Users, title: "Church Membership System", desc: "Manage member records including baptism dates, transfer letters, and Sabbath School class assignments." },
      { icon: CreditCard, title: "Digital Giving Platform", desc: "Enable members to give tithes and offerings digitally via mobile money and online payments." },
      { icon: BarChart3, title: "Conference Reporting", desc: "Generate comprehensive reports for local church, field, conference, and union levels." },
      { icon: Shield, title: "Department Management", desc: "Manage church departments including Sabbath School, Adventist Youth, and Community Services." },
      { icon: Bell, title: "Sabbath Announcements", desc: "Send weekly Sabbath program updates and church announcements to all members." },
    ],
    benefits: [
      "Conference-level financial visibility",
      "Automated tithe allocation system",
      "Sabbath School attendance tracking",
      "Department activity management",
      "Member transfer processing",
      "Multi-level organizational support",
    ],
  },
  pentecostal: {
    name: "Pentecostal",
    fullName: "Pentecostal Churches",
    icon: pentecostalIcon,
    accent: "hsl(25 90% 50%)",
    accentLight: "hsl(25 90% 50% / 0.08)",
    tagline: "Amplify Ministry Impact and Growth",
    description: "Kanisa Langu for Pentecostal churches provides dynamic tools for fast-growing ministries, from seed offering management to cell group tracking, crusade planning, and multi-branch operations.",
    features: [
      { icon: TrendingUp, title: "Ministry Fund Tracking", desc: "Monitor all ministry income including tithes, seed offerings, building funds, and mission contributions." },
      { icon: Users, title: "Cell Group Management", desc: "Organize and track cell groups, home fellowships, and ministry teams with leader assignments." },
      { icon: CreditCard, title: "Seed and Offering Platform", desc: "Enable digital seed offerings and pledges with mobile money integration and payment tracking." },
      { icon: BarChart3, title: "Growth Analytics", desc: "Track church growth metrics including attendance trends, new converts, and baptism records." },
      { icon: Shield, title: "Multi-Branch Management", desc: "Manage multiple church branches from a central dashboard with branch-level reporting." },
      { icon: Bell, title: "Crusade and Event Planning", desc: "Plan and promote crusades, revival meetings, and special services with automated notifications." },
    ],
    benefits: [
      "Multi-branch church management",
      "Cell group tracking and coordination",
      "Growth and attendance analytics",
      "Digital offering and pledge system",
      "Crusade and event management tools",
      "New convert follow-up system",
    ],
  },
};

export default function ChurchPage() {
  const { slug } = useParams<{ slug: string }>();
  const church = slug ? churchData[slug] : null;
  const isELCT = slug === "elct";

  if (!church) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <div className="text-center">
          <h1 className="text-3xl font-bold text-foreground mb-4">Church not found</h1>
          <Link to="/" className="text-secondary hover:underline font-medium">Back to home</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      <Navbar />

      {/* Hero */}
      <section className="relative bg-[hsl(220,30%,6%)] overflow-hidden">
        {/* Subtle grid */}
        <div
          className="absolute inset-0 opacity-[0.02]"
          style={{
            backgroundImage: `linear-gradient(hsl(0 0% 100%) 1px, transparent 1px), linear-gradient(90deg, hsl(0 0% 100%) 1px, transparent 1px)`,
            backgroundSize: "72px 72px",
          }}
        />
        {/* Accent glow */}
        <div className="absolute top-0 right-0 w-[600px] h-[600px] rounded-full blur-[200px] opacity-[0.07]" style={{ background: church.accent }} />
        <div className="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-primary/[0.08] blur-[160px]" />

        <div className="max-w-7xl mx-auto px-6 pt-32 pb-20 relative z-10">
          <Link to="/" className="inline-flex items-center gap-2 text-white/40 hover:text-white/70 transition-colors mb-12 text-sm font-medium">
            <ArrowLeft className="w-4 h-4" /> Back to home
          </Link>

          <div className="grid lg:grid-cols-2 gap-16 items-center">
            {/* Left: Text */}
            <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }}>
              <div className="flex items-center gap-4 mb-8">
                <img src={church.icon} alt={church.name} className="w-14 h-14 rounded-2xl bg-white/[0.06] p-2 border border-white/[0.06]" />
                <span className="text-sm font-bold text-white/30 uppercase tracking-widest">{church.name}</span>
              </div>

              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-white font-display tracking-tight leading-[1.08] mb-6">
                {church.fullName}
              </h1>

              <p className="text-lg font-semibold mb-3" style={{ color: church.accent }}>
                {church.tagline}
              </p>

              <p className="text-base text-white/40 leading-relaxed max-w-lg mb-10">
                {church.description}
              </p>

              <div className="flex flex-wrap gap-3">
                <a
                  href="https://play.google.com/store/apps/details?id=com.elerai.sewmr.kanisa_langu"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-7 py-3.5 bg-secondary text-secondary-foreground rounded-xl font-bold text-sm hover:-translate-y-0.5 transition-all duration-300"
                >
                  Get on Google Play
                </a>
                <a
                  href="https://apps.apple.com/app/id6741481584"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-7 py-3.5 text-white/50 border border-white/[0.08] rounded-xl font-medium text-sm hover:text-white hover:border-white/20 transition-all duration-300"
                >
                  Download on App Store
                </a>
              </div>
            </motion.div>

            {/* Right: Quick stats */}
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              className="hidden lg:block"
            >
              <div className="grid grid-cols-2 gap-4">
                {church.features.slice(0, 4).map((feat, i) => (
                  <motion.div
                    key={feat.title}
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ delay: 0.3 + i * 0.08 }}
                    className="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] hover:border-white/[0.12] transition-all duration-300 group"
                  >
                    <div className="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style={{ background: church.accentLight }}>
                      <feat.icon className="w-5 h-5" style={{ color: church.accent }} />
                    </div>
                    <h3 className="text-sm font-bold text-white/80 mb-1">{feat.title}</h3>
                    <p className="text-xs text-white/30 leading-relaxed line-clamp-2">{feat.desc}</p>
                  </motion.div>
                ))}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ELCT Church Structure Levels */}
      {isELCT && (
        <section className="py-24 border-b border-border">
          <div className="max-w-7xl mx-auto px-6">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="mb-14"
            >
              <span className="text-sm font-bold text-secondary uppercase tracking-widest">Full hierarchy support</span>
              <h2 className="mt-4 text-3xl sm:text-4xl font-bold text-foreground font-display tracking-tight">
                Every level of the ELCT structure
              </h2>
              <p className="mt-4 text-muted-foreground text-lg max-w-2xl">
                Kanisa Langu manages the complete ELCT organizational hierarchy, giving each level the tools it needs while maintaining seamless data flow across the entire structure.
              </p>
            </motion.div>

            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {elctLevels.map((level, i) => (
                <motion.div
                  key={level.name}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: i * 0.08 }}
                  className="p-6 rounded-2xl bg-card border border-border hover:border-secondary/30 hover:shadow-lg transition-all duration-300"
                >
                  <div className="flex items-center gap-3 mb-3">
                    <div className="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0">
                      <level.icon className="w-5 h-5 text-secondary" />
                    </div>
                    <div>
                      <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Level {i + 1}</span>
                      <h3 className="text-base font-bold text-foreground">{level.name}</h3>
                    </div>
                  </div>
                  <p className="text-sm text-muted-foreground leading-relaxed">{level.description}</p>
                </motion.div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Features */}
      <section className="py-24">
        <div className="max-w-7xl mx-auto px-6">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="mb-14"
          >
            <span className="text-sm font-bold text-secondary uppercase tracking-widest">Features</span>
            <h2 className="mt-4 text-3xl sm:text-4xl font-bold text-foreground font-display tracking-tight">
              What Kanisa Langu offers for {church.name}
            </h2>
          </motion.div>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            {church.features.map((feat, i) => (
              <motion.div
                key={feat.title}
                initial={{ opacity: 0, y: 24 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.06 }}
                className="group p-7 rounded-2xl bg-card border border-border hover:border-secondary/30 hover:shadow-lg transition-all duration-300"
              >
                <div className="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center mb-5">
                  <feat.icon className="w-6 h-6 text-secondary" />
                </div>
                <h3 className="text-lg font-bold text-foreground mb-2">{feat.title}</h3>
                <p className="text-sm text-muted-foreground leading-relaxed">{feat.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Benefits */}
      <section className="py-24 bg-muted/50">
        <div className="max-w-5xl mx-auto px-6">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-center mb-14"
          >
            <span className="text-sm font-bold text-secondary uppercase tracking-widest">Benefits</span>
            <h2 className="mt-4 text-3xl sm:text-4xl font-bold text-foreground font-display tracking-tight">
              Why {church.name} churches choose Kanisa Langu
            </h2>
          </motion.div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {church.benefits.map((benefit, i) => (
              <motion.div
                key={benefit}
                initial={{ opacity: 0, y: 16 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.06 }}
                className="flex items-start gap-3 p-5 rounded-xl bg-card border border-border"
              >
                <div className="w-6 h-6 rounded-full bg-secondary/10 flex items-center justify-center shrink-0 mt-0.5">
                  <Check className="w-3.5 h-3.5 text-secondary" />
                </div>
                <span className="text-sm font-semibold text-foreground">{benefit}</span>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-24">
        <div className="max-w-3xl mx-auto px-6 text-center">
          <motion.div initial={{ opacity: 0, y: 30 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}>
            <h2 className="text-3xl sm:text-4xl font-bold text-foreground font-display mb-5">
              Ready to transform your {church.name} operations?
            </h2>
            <p className="text-muted-foreground mb-10 text-lg">
              Download Kanisa Langu and start managing your church more effectively today.
            </p>
            <div className="flex flex-wrap justify-center gap-4">
              <a
                href="https://play.google.com/store/apps/details?id=com.elerai.sewmr.kanisa_langu"
                target="_blank"
                rel="noopener noreferrer"
                className="px-8 py-4 bg-secondary text-secondary-foreground rounded-2xl font-bold text-sm hover:opacity-90 transition-opacity"
              >
                Get on Google Play
              </a>
              <a
                href="https://apps.apple.com/app/id6741481584"
                target="_blank"
                rel="noopener noreferrer"
                className="px-8 py-4 bg-primary text-primary-foreground rounded-2xl font-bold text-sm hover:opacity-90 transition-opacity"
              >
                Download on App Store
              </a>
            </div>
          </motion.div>
        </div>
      </section>

      <Footer />
    </div>
  );
}
