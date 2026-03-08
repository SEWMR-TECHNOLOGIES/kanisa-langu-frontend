import { Link } from "react-router-dom";
import { ArrowLeft } from "lucide-react";
import logo from "../assets/logo.png";

export default function TermsPage() {
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
        <h1 className="text-4xl font-bold text-foreground font-display mb-2">Terms & Conditions</h1>
        <p className="text-sm text-muted-foreground mb-12">Last updated: March 8, 2026</p>

        <div className="prose-custom space-y-10">
          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">1. Acceptance of Terms</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              By accessing and using Kanisa Langu ("the Platform"), you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you must not use the Platform. These terms apply to all users, including church administrators, pastors, treasurers, and members.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">2. Description of Service</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              Kanisa Langu is a church management platform designed for ELCT, Roman Catholic, SDA, and Pentecostal churches in Tanzania. The Platform provides tools for financial management, member registry, communication, reporting, and administrative operations. SEWMR Technologies reserves the right to modify, suspend, or discontinue any part of the service at any time with reasonable notice.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">3. User Accounts</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              You are responsible for maintaining the confidentiality of your account credentials. You must provide accurate and complete information when creating an account. Each user must have their own account; sharing credentials is prohibited. You are responsible for all activities that occur under your account.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">4. Church Data and Ownership</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              All church data entered into the Platform remains the property of the respective church organization. SEWMR Technologies acts as a data processor and will not sell, share, or use church data for purposes other than providing the service. Churches may request export or deletion of their data at any time.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">5. Financial Transactions</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              The Platform facilitates financial record-keeping and may integrate with mobile money services. SEWMR Technologies is not a financial institution and is not liable for errors in third-party payment processing. Churches are responsible for verifying the accuracy of all financial records.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">6. Acceptable Use</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              You agree not to misuse the Platform. This includes but is not limited to: attempting unauthorized access to other accounts, using the Platform for illegal activities, uploading malicious content, or interfering with the Platform's infrastructure. Violation of these terms may result in account suspension or termination.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">7. Intellectual Property</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              The Platform, including its design, features, and content, is the intellectual property of SEWMR Technologies. You may not copy, modify, distribute, or reverse-engineer any part of the Platform without written permission.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">8. Limitation of Liability</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              SEWMR Technologies shall not be liable for any indirect, incidental, or consequential damages arising from your use of the Platform. The Platform is provided "as is" without warranties of any kind, express or implied. Our total liability shall not exceed the amount paid by you in the twelve months preceding the claim.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">9. Termination</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              Either party may terminate the agreement at any time. Upon termination, your right to use the Platform ceases immediately. SEWMR Technologies will retain your data for 30 days after termination, after which it may be permanently deleted unless otherwise required by law.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">10. Governing Law</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              These Terms shall be governed by and construed in accordance with the laws of the United Republic of Tanzania. Any disputes arising from these terms shall be resolved in the courts of Tanzania.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">11. Contact</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              For questions about these Terms, contact SEWMR Technologies at{" "}
              <a href="mailto:hello@sewmrtechnologies.com" className="text-secondary hover:underline">hello@sewmrtechnologies.com</a>.
            </p>
          </section>
        </div>
      </div>
    </div>
  );
}
