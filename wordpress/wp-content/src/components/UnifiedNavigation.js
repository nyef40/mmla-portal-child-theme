"use client"

import { useState, useEffect } from "react"
import { Link, useLocation } from "react-router-dom"
import { isAuthenticated, logout } from "../utils/auth"

function UnifiedNavigation() {
  const [isLoggedIn, setIsLoggedIn] = useState(false)
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const location = useLocation()

  useEffect(() => {
    setIsLoggedIn(isAuthenticated())
  }, [location])

  const isPortalPage = location.pathname.startsWith("/portal") || location.pathname.startsWith("/dashboard")

  const handleLogout = async () => {
    await logout()
    setIsLoggedIn(false)
    window.location.href = "/"
  }

  return (
    <nav className="unified-navigation">
      <div className="nav-container">
        {/* Brand */}
        <div className="nav-brand">
          <Link to="/">
            <img
              src="/wp-content/themes/blocksy-child/portal/dist/logo.png"
              alt="Mobile Medical LA"
              className="brand-logo"
            />
            <span className="brand-text">{isPortalPage ? "Mobile Medical LA Portal" : "Mobile Medical LA"}</span>
          </Link>
        </div>

        {/* Mobile Menu Toggle */}
        <button
          className="mobile-menu-toggle"
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          aria-label="Toggle menu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

        {/* Navigation Links */}
        <div className={`nav-links ${mobileMenuOpen ? "mobile-open" : ""}`}>
          {!isPortalPage ? (
            /* Main Website Navigation */
            <>
              <Link to="/" className={location.pathname === "/" ? "active" : ""}>
                Home
              </Link>
              <Link to="/our-services" className={location.pathname === "/our-services" ? "active" : ""}>
                Our Services
              </Link>
              <Link to="/about-us" className={location.pathname === "/about-us" ? "active" : ""}>
                About Us
              </Link>
              <Link to="/ivig-news" className={location.pathname === "/ivig-news" ? "active" : ""}>
                IVIG News
              </Link>
              <Link to="/portal" className="portal-link">
                Portal
              </Link>
            </>
          ) : (
            /* Portal Navigation */
            <>
              <Link to="/portal" className={location.pathname === "/portal" ? "active" : ""}>
                Home
              </Link>
              {isLoggedIn && (
                <>
                  <Link to="/dashboard" className={location.pathname === "/dashboard" ? "active" : ""}>
                    Dashboard
                  </Link>
                  <Link to="/portal-resources" className={location.pathname === "/portal-resources" ? "active" : ""}>
                    Resources
                  </Link>
                  <Link to="/portal-referrals" className={location.pathname === "/portal-referrals" ? "active" : ""}>
                    Referrals
                  </Link>
                  <Link to="/portal-profile" className={location.pathname === "/portal-profile" ? "active" : ""}>
                    Profile
                  </Link>
                </>
              )}
            </>
          )}

          {/* Auth Links */}
          <div className="nav-auth">
            {isLoggedIn ? (
              <button onClick={handleLogout} className="btn-logout">
                Logout
              </button>
            ) : (
              <>
                <Link to="/portal-login" className="btn-login">
                  Login
                </Link>
                <Link to="/register" className="btn-register">
                  Register
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  )
}

export default UnifiedNavigation
