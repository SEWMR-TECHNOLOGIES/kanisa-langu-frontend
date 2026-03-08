import { Link } from "react-router-dom";
import { ArrowLeft } from "lucide-react";
import logo from "../assets/logo.png";

export default function CookiesPage() {
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
        <h1 className="text-4xl font-bold text-foreground font-display mb-2">Cookie Policy</h1>
        <p className="text-sm text-muted-foreground mb-12">Last updated: March 8, 2026</p>

        <div className="space-y-10">
          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">1. What Are Cookies</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              Cookies are small text files stored on your device when you visit a website. They help us provide a better experience by remembering your preferences, keeping you signed in, and understanding how you use our platform. This policy explains what cookies we use and why.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">2. Cookies We Use</h2>
            <div className="space-y-4">
              <div className="p-4 rounded-xl bg-card border border-border">
                <h3 className="text-sm font-bold text-foreground mb-1">Essential Cookies</h3>
                <p className="text-xs text-muted-foreground leading-relaxed">
                  Required for the platform to function. These handle authentication, security, and basic functionality. They cannot be disabled.
                </p>
              </div>
              <div className="p-4 rounded-xl bg-card border border-border">
                <h3 className="text-sm font-bold text-foreground mb-1">Functional Cookies</h3>
                <p className="text-xs text-muted-foreground leading-relaxed">
                  Remember your preferences such as language settings, display options, and recently viewed pages. These improve your experience but are not strictly necessary.
                </p>
              </div>
              <div className="p-4 rounded-xl bg-card border border-border">
                <h3 className="text-sm font-bold text-foreground mb-1">Analytics Cookies</h3>
                <p className="text-xs text-muted-foreground leading-relaxed">
                  Help us understand how users interact with the platform. We use this data to improve features and fix issues. All analytics data is anonymized and aggregated.
                </p>
              </div>
            </div>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">3. Managing Cookies</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              You can control cookies through your browser settings. Most browsers allow you to block or delete cookies. However, disabling essential cookies may prevent parts of the platform from working correctly. You can also update your cookie preferences at any time through the cookie consent banner.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">4. Third-Party Cookies</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              We may use third-party services that set their own cookies for analytics and payment processing. These third parties have their own privacy policies governing the use of cookies. We do not control third-party cookies but carefully select partners who share our commitment to user privacy.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">5. Updates to This Policy</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              We may update this Cookie Policy to reflect changes in our practices or applicable regulations. We will post the updated policy on this page with a revised date.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-bold text-foreground font-display mb-3">6. Contact</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              For questions about our use of cookies, contact us at{" "}
              <a href="mailto:hello@sewmrtechnologies.com" className="text-secondary hover:underline">hello@sewmrtechnologies.com</a>.
            </p>
          </section>
        </div>
      </div>
    </div>
  );
}
