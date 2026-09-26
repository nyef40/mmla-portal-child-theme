const AUDIENCES = [
  {
    tag: 'REFERRING PHYSICIANS',
    title: 'Faster, cleaner handoffs',
    items: [
      'Orders and Plan of Care signed electronically — no faxed paperwork to chase down',
      'Referral status visible in real time, instead of a callback the next day',
      "Documentation formatted to your organization's standards on request",
    ],
  },
  {
    tag: 'PATIENTS & FAMILIES',
    title: 'One chart, start to finish',
    items: [
      'Your history follows you from referral to discharge — nothing re-entered by hand',
      'Every visit, medication change, and care plan update logged the same day',
      "Whoever on your care team you're speaking with is reading the same record",
    ],
  },
  {
    tag: 'PARTNER AGENCIES & FACILITIES',
    title: 'Coordination without the phone tag',
    items: [
      'Secure inter-office notes shared directly between care teams',
      'Audit-ready documentation attached to every encounter',
      'Compliance tracked against OASIS, HIPAA, and CMS home health standards',
    ],
  },
];

export default function AudienceSection() {
  return (
    <section className="block">
      <div className="section-head">
        <p className="eyebrow">In Practice</p>
        <h2>What actually changes when your care runs on one system</h2>
        <p>Not a sales pitch for software — just what's different for the people who deal with
          us day to day.</p>
      </div>

      <div className="audience-grid">
        {AUDIENCES.map((a) => (
          <div className="audience-col" key={a.tag}>
            <p className="tag">{a.tag}</p>
            <h3>{a.title}</h3>
            <ul>
              {a.items.map((item) => <li key={item}>{item}</li>)}
            </ul>
          </div>
        ))}
      </div>
    </section>
  );
}
