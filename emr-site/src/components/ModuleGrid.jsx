const MODULES = [
  { num: '01', tag: 'MODULE — PATIENTS', title: 'Charts & Care Plans', desc: "Each patient's history, medications, and individualized Plan of Care live in a single chart that updates continuously, not once a week." },
  { num: '02', tag: 'MODULE — COORDINATION', title: 'Care Coordination', desc: 'Secure notes and inter-office communication keep nursing, physicians, and support staff working from the same page — literally.' },
  { num: '03', tag: 'MODULE — SCHEDULING', title: 'Visit Tracking', desc: 'Every home visit, supervisory check-in, and infusion appointment scheduled and confirmed against real staff availability.' },
  { num: '04', tag: 'MODULE — COMPLIANCE', title: 'Audit Trail', desc: 'Every access, edit, and signature is logged and time-stamped — built for CMS and HIPAA audit requirements, not added after the fact.' },
  { num: '05', tag: 'MODULE — BILLING', title: 'Billing & Documentation', desc: 'Claims and eligibility checks move through the same system that manages the clinical record, cutting down errors from re-entry.' },
  { num: '06', tag: 'MODULE — ANALYTICS', title: 'Reporting', desc: 'Agency leadership tracks visit compliance and care quality in real time — not reconstructed at the end of the month.' },
];

export default function ModuleGrid() {
  return (
    <section className="block module-section" id="modules" style={{ paddingInline: 'clamp(20px, 4vw, 40px)' }}>
      <div className="section-head">
        <p className="eyebrow">The Platform</p>
        <h2>Six ways myEMR keeps care moving</h2>
        <p>The same categories our own staff work in every day — described plainly, without a
          screenshot of anyone's actual chart.</p>
      </div>

      <div className="module-grid">
        {MODULES.map((m) => (
          <div className="module-card" key={m.num}>
            <span className="m-tag">{m.tag}</span>
            <h3><span className="m-num">{m.num}</span>&nbsp; {m.title}</h3>
            <p>{m.desc}</p>
          </div>
        ))}
      </div>
    </section>
  );
}
