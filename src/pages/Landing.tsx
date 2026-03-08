import Navbar from "../components/landing/Navbar";
import Hero from "../components/landing/Hero";
import Churches from "../components/landing/Churches";
import Features from "../components/landing/Features";
import About from "../components/landing/About";
import HowItWorks from "../components/landing/HowItWorks";
import FAQ from "../components/landing/FAQ";
import CTA from "../components/landing/CTA";
import Footer from "../components/landing/Footer";

export default function Landing() {
  return (
    <div className="min-h-screen bg-background overflow-x-hidden">
      <Navbar />
      <Hero />
      <Churches />
      <Features />
      <About />
      <HowItWorks />
      <FAQ />
      <CTA />
      <Footer />
    </div>
  );
}