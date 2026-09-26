import SiteNav from './components/SiteNav.jsx';
import Hero from './components/Hero.jsx';
import AudienceSection from './components/AudienceSection.jsx';
import ModuleGrid from './components/ModuleGrid.jsx';
import ComplianceStrip from './components/ComplianceStrip.jsx';
import ClosingCta from './components/ClosingCta.jsx';
import Footer from './components/Footer.jsx';

export default function App() {
  return (
    <div className="wrap">
      <SiteNav />
      <Hero />
      <AudienceSection />
      <ModuleGrid />
      <ComplianceStrip />
      <ClosingCta />
      <Footer />
    </div>
  );
}
