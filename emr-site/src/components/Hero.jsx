const CHIPS = [
  'HIPAA-Compliant',
  'Cloud-Hosted & Encrypted',
  'OASIS-Standard Documentation',
  '24/7 Clinician Access',
];

export default function Hero() {
  return (
    <section className="hero" id="top">
      <div>
        <p className="eyebrow on-navy">Care Coordination · How We Operate</p>
        <h1>Every referral, every visit, every record — tracked the moment it happens.</h1>
        <p>
          Mobile Medical LA runs on <strong>Netsmart myUnity</strong>, a certified, cloud-based
          electronic medical records platform built specifically for home health and home
          infusion care. Formerly known as deVero, it's the same system our nurses, physicians,
          and office staff all work from — so nothing about your care lives in a filing cabinet
          or a fax machine.
        </p>
        <p className="cite">
          <b>What that means in practice:</b> one chart, updated in real time, visible to
          everyone actually involved in your care.
        </p>

        <div className="chip-row">
          {CHIPS.map((chip) => (
            <span className="chip" key={chip}>{chip}</span>
          ))}
        </div>

        <div className="cta-row">
          <a className="btn solid" href="#modules">See what the system actually does →</a>
          <a className="btn ghost" href="https://mobilemedicalla.com/portal-referrals/">Refer a Patient</a>
        </div>
      </div>

      <div className="chart-stack" aria-hidden="true">
        <div className="rec-card card-1">
          <div className="rc-top"><span className="rc-id">CHART · 3381-A</span><span className="rc-dot"></span></div>
          <div className="bar b85"></div><div className="bar b60"></div><div className="bar accent b40"></div>
        </div>
        <div className="rec-card card-2">
          <div className="rc-top"><span className="rc-id">PLAN OF CARE</span><span className="rc-dot"></span></div>
          <div className="bar b100"></div><div className="bar b60 accent"></div><div className="bar b85"></div>
        </div>
        <div className="rec-card card-3">
          <div className="rc-top"><span className="rc-id">VISIT NOTE · TODAY</span><span className="rc-dot"></span></div>
          <div className="bar b60"></div><div className="bar b40 accent"></div>
        </div>
        <div className="sync-pill"><span className="pulse"></span> Synced live</div>
      </div>
    </section>
  );
}
