import { useParams, Link } from "react-router-dom";
import { motion } from "framer-motion";
import { ArrowLeft, Check, TrendingUp, Users, CreditCard, BarChart3, Shield, Bell, Building2, MapPin, Church, Home, UsersRound } from "lucide-react";
import logo from "../assets/logo.png";
import elctIcon from "../assets/elct-icon.png";
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
  color: string;
  gradient: string;
  description: string;
  tagline: string;
  features: { icon: typeof TrendingUp; title: string; desc: string }[];
  benefits: string[];
}> = {
  elct: {
    name: "ELCT",
    fullName: "Evangelical Lutheran Church in Tanzania",
    icon: elctIcon,
    color: "from-blue-900 to-blue-700",
    gradient: "from-blue-900/10 to-blue-700/5",
    tagline: "Streamline Diocese & Parish Operations",
    description: "Kanisa Langu for ELCT provides comprehensive tools tailored to the Lutheran church structure, from Diocese management to individual parish tracking, offering full visibility into financial operations and member engagement across all levels.",
    features: [
      { icon: TrendingUp, title: "Diocese Revenue Tracking", desc: "Monitor income across all parishes under a diocese with real-time dashboards and aggregated reporting." },
      { icon: Users, title: "Parish Member Registry", desc: "Manage congregation records with baptism, confirmation, and membership tracking across all parishes." },
      { icon: CreditCard, title: "Offering & Tithe Collection", desc: "Digital collection system supporting M-Pesa, Tigo Pesa, and bank transfers for seamless giving." },
      { icon: BarChart3, title: "Multi-Level Reporting", desc: "Generate reports at parish, district, and diocese levels with customizable date ranges and categories." },
      { icon: Shield, title: "Role-Based Access", desc: "Assign roles to bishops, pastors, evangelists, and secretaries with granular permission controls." },
      { icon: Bell, title: "Congregation Notifications", desc: "Send targeted SMS and push notifications to specific parishes or the entire diocese." },
    ],
    benefits: [
      "Complete diocese-level financial oversight",
      "Hierarchical church structure support",
      "Automated monthly & annual reports",
      "Multi-parish management from one dashboard",
      "Secure data with role-based access control",
      "Integration with mobile money platforms",
    ],
  },
  "roman-catholic": {
    name: "Roman Catholic",
    fullName: "Roman Catholic Church",
    icon: rcIcon,
    color: "from-red-900 to-red-700",
    gradient: "from-red-900/10 to-red-700/5",
    tagline: "Empower Parish & Diocese Administration",
    description: "Kanisa Langu for Roman Catholic churches provides purpose-built tools for managing parish operations, sacramental records, and diocesan oversight, from small parishes to large archdioceses.",
    features: [
      { icon: TrendingUp, title: "Parish Financial Management", desc: "Track all parish income streams including collections, donations, and special fundraising campaigns." },
      { icon: Users, title: "Sacramental Records", desc: "Maintain comprehensive records of baptisms, confirmations, marriages, and other sacraments." },
      { icon: CreditCard, title: "Donation Management", desc: "Facilitate regular and one-time donations through mobile money and card payments with receipts." },
      { icon: BarChart3, title: "Deanery & Diocese Reports", desc: "Consolidate financial and operational data across deaneries for diocese-level insights." },
      { icon: Shield, title: "Clergy & Staff Management", desc: "Manage roles for parish priests, deacons, catechists, and administrative staff." },
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
    color: "from-blue-800 to-cyan-700",
    gradient: "from-blue-800/10 to-cyan-700/5",
    tagline: "Strengthen Conference & Church Operations",
    description: "Kanisa Langu for SDA churches supports the unique organizational structure, from local churches to conferences and unions, with tools designed for Sabbath operations, tithe management, and member care.",
    features: [
      { icon: TrendingUp, title: "Tithe & Offering Management", desc: "Track tithes, offerings, and special funds with automatic allocation to conference and union levels." },
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
    color: "from-orange-700 to-amber-600",
    gradient: "from-orange-700/10 to-amber-600/5",
    tagline: "Amplify Ministry Impact & Growth",
    description: "Kanisa Langu for Pentecostal churches provides dynamic tools for fast-growing ministries, from seed offering management to cell group tracking, crusade planning, and multi-branch operations.",
    features: [
      { icon: TrendingUp, title: "Ministry Fund Tracking", desc: "Monitor all ministry income including tithes, seed offerings, building funds, and mission contributions." },
      { icon: Users, title: "Cell Group Management", desc: "Organize and track cell groups, home fellowships, and ministry teams with leader assignments." },
      { icon: CreditCard, title: "Seed & Offering Platform", desc: "Enable digital seed offerings and pledges with mobile money integration and payment tracking." },
      { icon: BarChart3, title: "Growth Analytics", desc: "Track church growth metrics including attendance trends, new converts, and baptism records." },
      { icon: Shield, title: "Multi-Branch Management", desc: "Manage multiple church branches from a central dashboard with branch-level reporting." },
      { icon: Bell, title: "Crusade & Event Planning", desc: "Plan and promote crusades, revival meetings, and special services with automated notifications." },
    ],
    benefits: [
      "Multi-branch church management",
      "Cell group tracking & coordination",
      "Growth and attendance analytics",
      "Digital offering & pledge system",
      "Crusade & event management tools",
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
      {/* Hero */}
      <section className={`relative bg-gradient-to-br ${church.color} min-h-[60vh] flex items-center`}>
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.08),transparent_60%)]" />
        <div className="max-w-7xl mx-auto px-6 py-32 relative z-10 w-full">
          <Link to="/" className="inline-flex items-center gap-2 text-white/60 hover:text-white transition-colors mb-10 text-sm font-medium">
            <ArrowLeft className="w-4 h-4" /> Back to Kanisa Langu
          </Link>
          <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }}>
            <div className="flex items-center gap-5 mb-6">
              <img src={church.icon} alt={church.name} className="w-16 h-16 rounded-2xl bg-white/10 p-2" />
              <div>
                <span className="text-white/50 text-sm font-medium">{church.name}</span>
                <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-white font-display tracking-tight">{church.fullName}</h1>
              </div>
            </div>
            <p className="text-xl text-white/70 font-medium mb-2">{church.tagline}</p>
            <p className="text-base text-white/50 max-w-2xl leading-relaxed mt-4">{church.description}</p>
          </motion.div>
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
              <span className="text-sm font-bold text-secondary uppercase tracking-widest">
                Full hierarchy support
              </span>
              <h2 className="mt-4 text-3xl sm:text-4xl font-bold text-foreground font-display tracking-tight">
                Every level of the ELCT structure
              </h2>
              <p className="mt-4 text-muted-foreground text-lg max-w-2xl">
                Kanisa Langu manages the complete ELCT organizational hierarchy, giving each level the tools it needs while maintaining seamless data flow across the entire structure.
              </p>
            </motion.div>

            <div className="relative">
              {/* Connecting line */}
              <div className="hidden lg:block absolute left-1/2 top-0 bottom-0 w-px bg-border -translate-x-1/2" />

              <div className="grid gap-6">
                {elctLevels.map((level, i) => (
                  <motion.div
                    key={level.name}
                    initial={{ opacity: 0, x: i % 2 === 0 ? -30 : 30 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: i * 0.1 }}
                    className={`flex items-center gap-6 ${i % 2 === 0 ? 'lg:flex-row' : 'lg:flex-row-reverse'}`}
                  >
                    <div className={`flex-1 ${i % 2 === 0 ? 'lg:text-right' : 'lg:text-left'}`}>
                      <div className={`p-6 rounded-2xl bg-card border border-border hover:border-secondary/30 hover:shadow-lg transition-all duration-300 inline-block max-w-md ${i % 2 === 0 ? 'lg:ml-auto' : ''}`}>
                        <div className={`flex items-center gap-3 mb-3 ${i % 2 === 0 ? 'lg:flex-row-reverse' : ''}`}>
                          <div className="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0">
                            <level.icon className="w-5 h-5 text-secondary" />
                          </div>
                          <div>
                            <span className="text-xs font-bold text-muted-foreground uppercase tracking-wider">Level {i + 1}</span>
                            <h3 className="text-lg font-bold text-foreground">{level.name}</h3>
                          </div>
                        </div>
                        <p className={`text-sm text-muted-foreground leading-relaxed ${i % 2 === 0 ? 'lg:text-right' : ''}`}>{level.description}</p>
                      </div>
                    </div>

                    {/* Center dot */}
                    <div className="hidden lg:flex w-4 h-4 rounded-full bg-secondary border-4 border-background shrink-0 z-10" />

                    <div className="flex-1 hidden lg:block" />
                  </motion.div>
                ))}
              </div>
            </div>
          </div>
        </section>
      )}

      {/* Features */}
      <section className="py-24">
        <div className="max-w-7xl mx-auto px-6">
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-3xl sm:text-4xl font-bold text-foreground font-display mb-14"
          >
            What Kanisa Langu offers for {church.name}
          </motion.h2>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {church.features.map((feat, i) => (
              <motion.div
                key={feat.title}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.08 }}
                className={`group p-7 rounded-2xl bg-gradient-to-br ${church.gradient} border border-border hover:border-secondary/30 hover:shadow-lg transition-all duration-300`}
              >
                <feat.icon className="w-8 h-8 text-secondary mb-4" />
                <h3 className="text-lg font-bold text-foreground mb-2">{feat.title}</h3>
                <p className="text-sm text-muted-foreground leading-relaxed">{feat.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Benefits */}
      <section className={`py-24 bg-gradient-to-br ${church.gradient}`}>
        <div className="max-w-4xl mx-auto px-6">
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-3xl sm:text-4xl font-bold text-foreground font-display mb-12 text-center"
          >
            Key Benefits
          </motion.h2>
          <div className="grid sm:grid-cols-2 gap-5">
            {church.benefits.map((benefit, i) => (
              <motion.div
                key={benefit}
                initial={{ opacity: 0, x: i % 2 === 0 ? -20 : 20 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.08 }}
                className="flex items-center gap-4 p-5 rounded-xl bg-card border border-border"
              >
                <div className="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center shrink-0">
                  <Check className="w-4 h-4 text-secondary" />
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
                className="px-8 py-4 bg-primary text-primary-foreground rounded-2xl font-bold text-sm hover:opacity-90 transition-opacity"
              >
                Get on Google Play
              </a>
              <a
                href="https://apps.apple.com/app/id6741481584"
                target="_blank"
                rel="noopener noreferrer"
                className="px-8 py-4 bg-foreground text-background rounded-2xl font-bold text-sm hover:opacity-90 transition-opacity"
              >
                Download on App Store
              </a>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-border py-10">
        <div className="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <Link to="/" className="flex items-center gap-2">
            <img src={logo} alt="Kanisa Langu" className="h-8 w-8" />
            <span className="font-bold text-foreground">Kanisa Langu</span>
          </Link>
          <p className="text-sm text-muted-foreground">&copy; {new Date().getFullYear()} SEWMR Technologies</p>
        </div>
      </footer>
    </div>
  );
}