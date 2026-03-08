import { Link } from "react-router-dom";
import { ArrowLeft } from "lucide-react";
import logo from "../assets/logo.png";

export default function PrivacyPage() {
  return (
    <div className="min-h-screen bg-background">
      <nav className="border-b border-border bg-card">
        <div className="max-w-4xl mx-auto px-6 py-4 flex items-center gap-4">
          <Link to="/" className="flex items-center gap-2 text-muted-foreground hover:text-foreground transition-colors text-sm">
            <ArrowLeft className="w-4 h-4" />
            Back
          </Link>
          <div className="h-4 w-px bg-border" />
          <Link to="/" className="flex items-center gap-2">
            <img src={logo} alt="Kanisa Langu" className="h-6 w-6" />
            <span className="font-bold text-foreground text-sm">Kanisa Langu</span>
          </Link>
        </div>
      </nav>

      <div className="max-w-4xl mx-auto px-6 py-16">
        <h1 className="text-4xl font-bold text-foreground font-display mb-2">Privacy Policy</h1>
        <p className="text-sm text-muted-foreground mb-12">Last updated: March 8, 2026</p>

        <div className="space-y-10">
          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">1. Introduction</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              SEWMR Technologies ("we", "our", "us") operates the Kanisa Langu platform. This Privacy Policy explains how we collect, use, store, and protect your personal information when you use our services. We are committed to protecting the privacy of all users, including church administrators, leaders, and members.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">2. Information We Collect</h2>
            <p className="text-sm text-muted-foreground leading-relaxed mb-3">We collect the following types of information:</p>
            <ul className="list-disc list-inside text-sm text-muted-foreground leading-relaxed space-y-1.5 ml-2">
              <li><strong className="text-foreground">Account Information:</strong> Name, email address, phone number, and church affiliation</li>
              <li><strong className="text-foreground">Church Data:</strong> Member records, financial records, attendance data, and administrative records</li>
              <li><strong className="text-foreground">Usage Data:</strong> How you interact with the Platform, device information, and log data</li>
              <li><strong className="text-foreground">Payment Data:</strong> Transaction records processed through integrated payment services</li>
            </ul>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">3. How We Use Your Information</h2>
            <ul className="list-disc list-inside text-sm text-muted-foreground leading-relaxed space-y-1.5 ml-2">
              <li>To provide and maintain the Kanisa Langu platform</li>
              <li>To process and manage church financial records</li>
              <li>To send service-related notifications and updates</li>
              <li>To improve our platform based on usage patterns</li>
              <li>To provide customer support</li>
              <li>To comply with legal obligations</li>
            </ul>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">4. Data Storage and Security</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              We use industry-standard security measures including encryption, secure servers, and access controls to protect your data. All data is stored on secure cloud infrastructure with automated backups. We regularly review and update our security practices to protect against unauthorized access, alteration, or destruction of data.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">5. Data Sharing</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              We do not sell your personal information to third parties. We may share data only in the following circumstances: with your explicit consent, with service providers who assist in operating the Platform (under strict confidentiality agreements), or when required by law or legal process.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">6. Your Rights</h2>
            <ul className="list-disc list-inside text-sm text-muted-foreground leading-relaxed space-y-1.5 ml-2">
              <li>Access and review your personal data</li>
              <li>Request correction of inaccurate data</li>
              <li>Request deletion of your data</li>
              <li>Export your data in a portable format</li>
              <li>Opt out of non-essential communications</li>
              <li>Withdraw consent at any time</li>
            </ul>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">7. Children's Privacy</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              The Platform is not intended for children under 13. We do not knowingly collect personal information from children. If we discover that a child under 13 has provided personal information, we will delete it promptly.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">8. Changes to This Policy</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              We may update this Privacy Policy from time to time. We will notify you of significant changes through the Platform or via email. Your continued use of the Platform after changes constitutes acceptance of the updated policy.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">9. Contact Us</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              If you have questions about this Privacy Policy or your data, contact us at{" "}
              <a href="mailto:hello@sewmrtechnologies.com" className="text-secondary hover:underline">hello@sewmrtechnologies.com</a>.
            </p>
          </section>
        </div>
      </div>
    </div>
  );
}
