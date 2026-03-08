import { Link } from "react-router-dom";
import logo from "../../assets/logo.png";

export default function Footer() {
  return (
    <footer className="border-t border-border py-12">
      <div className="max-w-7xl mx-auto px-6">
        <div className="flex flex-col sm:flex-row items-center justify-between gap-6">
          <Link to="/" className="flex items-center gap-2.5">
            <img src={logo} alt="Kanisa Langu" className="h-8 w-8" />
            <span className="text-base font-bold text-foreground">Kanisa Langu</span>
          </Link>
          <p className="text-sm text-muted-foreground">
            © {new Date().getFullYear()} SEWMR Technologies. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}