const BADGES = ['HIPAA', 'OASIS-E', 'CMS HOME HEALTH COP', 'ENCRYPTED AT REST'];

export default function ComplianceStrip() {
  return (
    <section className="block" style={{ paddingBlock: '0 40px' }}>
      <div className="compliance">
        <div className="cl-copy">
          <p className="eyebrow on-navy">Built On Standards</p>
          <h2>Not a promise — a paper trail</h2>
          <p>Hosted on infrastructure built to HIPAA and CMS home health requirements, with
            every record change attributable, time-stamped, and retrievable on request.</p>
        </div>
        <div className="badge-grid">
          {BADGES.map((b) => <span className="badge" key={b}>{b}</span>)}
        </div>
      </div>
    </section>
  );
}
