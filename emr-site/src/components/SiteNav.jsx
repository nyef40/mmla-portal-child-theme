export default function SiteNav() {
  return (
    <div className="site-nav">
      <a className="brand" href="/">
        <span className="dot" aria-hidden="true"></span> Mobile Medical LA
      </a>
      <div className="crumbs">
        <a href="/">Home</a> &nbsp;/&nbsp;{' '}
        <a href="#top">Our Technology</a>
      </div>
    </div>
  );
}
