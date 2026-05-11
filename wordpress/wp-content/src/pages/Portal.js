"use client"
import ReactDOM from "react-dom"

// Get portalData from global window object
const portalData = typeof window !== "undefined" ? window.portalData : {}

const Portal = () => {
  const isLoggedIn = portalData.isLoggedIn || false

  return (
    <div className="portal-home">
      <div className="portal-content-area">
        <div
          className="portal-hero"
          style={{
            background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 100%)",
            color: "white",
            padding: "60px 40px",
            borderRadius: "12px",
            textAlign: "center",
            marginBottom: "40px",
            boxShadow: "0 8px 32px rgba(10, 61, 98, 0.3)",
          }}
        >
          <h1 style={{ fontSize: "2.5rem", marginBottom: "20px", fontWeight: "700" }}>
            Welcome to Mobile Medical LA Portal
          </h1>
          <p style={{ fontSize: "1.2rem", marginBottom: "30px", opacity: "0.9" }}>
            Streamline your healthcare management with our comprehensive provider portal
          </p>

          {!isLoggedIn ? (
            <div className="portal-auth-buttons">
              <a
                href="/portal-login/"
                className="button"
                style={{
                  display: "inline-block",
                  padding: "15px 30px",
                  background: "rgba(255, 255, 255, 0.2)",
                  color: "white",
                  textDecoration: "none",
                  borderRadius: "8px",
                  fontWeight: "600",
                  margin: "0 10px",
                  border: "2px solid rgba(255, 255, 255, 0.3)",
                  transition: "all 0.3s ease",
                }}
              >
                Sign In
              </a>
              <a
                href="/register/"
                className="button button-secondary"
                style={{
                  display: "inline-block",
                  padding: "15px 30px",
                  background: "white",
                  color: "#0a3d62",
                  textDecoration: "none",
                  borderRadius: "8px",
                  fontWeight: "600",
                  margin: "0 10px",
                  transition: "all 0.3s ease",
                }}
              >
                New User
              </a>
            </div>
          ) : (
            <div className="portal-welcome-back">
              <p style={{ fontSize: "1.1rem", marginBottom: "20px" }}>
                Welcome back, {portalData.currentUser?.firstName || portalData.currentUser?.username}!
              </p>
              <a
                href="/dashboard/"
                className="button"
                style={{
                  display: "inline-block",
                  padding: "15px 30px",
                  background: "white",
                  color: "#0a3d62",
                  textDecoration: "none",
                  borderRadius: "8px",
                  fontWeight: "600",
                  transition: "all 0.3s ease",
                }}
              >
                Go to Dashboard
              </a>
            </div>
          )}
        </div>

        <div className="portal-features">
          <h2
            style={{
              background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 100%)",
              WebkitBackgroundClip: "text",
              WebkitTextFillColor: "transparent",
              backgroundClip: "text",
              textAlign: "center",
              marginBottom: "40px",
              fontSize: "2rem",
            }}
          >
            Portal Features
          </h2>

          <div
            className="features-grid"
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
              gap: "25px",
            }}
          >
            <div
              className="feature-card"
              style={{
                background: "linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)",
                border: "1px solid rgba(10, 61, 98, 0.1)",
                borderRadius: "12px",
                padding: "30px",
                textAlign: "center",
                boxShadow: "0 8px 32px rgba(10, 61, 98, 0.1)",
                position: "relative",
                overflow: "hidden",
              }}
            >
              <div
                style={{
                  content: '""',
                  position: "absolute",
                  top: "0",
                  left: "0",
                  right: "0",
                  height: "4px",
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 50%, #2980b9 100%)",
                }}
              ></div>
              <h3
                style={{
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 100%)",
                  WebkitBackgroundClip: "text",
                  WebkitTextFillColor: "transparent",
                  backgroundClip: "text",
                  marginBottom: "15px",
                  fontSize: "1.4rem",
                }}
              >
                📊 Dashboard
              </h3>
              <p>Access your personalized dashboard with quick links and activity overview.</p>
            </div>

            <div
              className="feature-card"
              style={{
                background: "linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)",
                border: "1px solid rgba(10, 61, 98, 0.1)",
                borderRadius: "12px",
                padding: "30px",
                textAlign: "center",
                boxShadow: "0 8px 32px rgba(10, 61, 98, 0.1)",
                position: "relative",
                overflow: "hidden",
              }}
            >
              <div
                style={{
                  content: '""',
                  position: "absolute",
                  top: "0",
                  left: "0",
                  right: "0",
                  height: "4px",
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 50%, #2980b9 100%)",
                }}
              ></div>
              <h3
                style={{
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 100%)",
                  WebkitBackgroundClip: "text",
                  WebkitTextFillColor: "transparent",
                  backgroundClip: "text",
                  marginBottom: "15px",
                  fontSize: "1.4rem",
                }}
              >
                👤 Profile Management
              </h3>
              <p>Update your professional information and contact details.</p>
            </div>

            <div
              className="feature-card"
              style={{
                background: "linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)",
                border: "1px solid rgba(10, 61, 98, 0.1)",
                borderRadius: "12px",
                padding: "30px",
                textAlign: "center",
                boxShadow: "0 8px 32px rgba(10, 61, 98, 0.1)",
                position: "relative",
                overflow: "hidden",
              }}
            >
              <div
                style={{
                  content: '""',
                  position: "absolute",
                  top: "0",
                  left: "0",
                  right: "0",
                  height: "4px",
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 50%, #2980b9 100%)",
                }}
              ></div>
              <h3
                style={{
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 100%)",
                  WebkitBackgroundClip: "text",
                  WebkitTextFillColor: "transparent",
                  backgroundClip: "text",
                  marginBottom: "15px",
                  fontSize: "1.4rem",
                }}
              >
                📚 Resources
              </h3>
              <p>Access important documents, guidelines, and educational materials.</p>
            </div>

            <div
              className="feature-card"
              style={{
                background: "linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)",
                border: "1px solid rgba(10, 61, 98, 0.1)",
                borderRadius: "12px",
                padding: "30px",
                textAlign: "center",
                boxShadow: "0 8px 32px rgba(10, 61, 98, 0.1)",
                position: "relative",
                overflow: "hidden",
              }}
            >
              <div
                style={{
                  content: '""',
                  position: "absolute",
                  top: "0",
                  left: "0",
                  right: "0",
                  height: "4px",
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 50%, #2980b9 100%)",
                }}
              ></div>
              <h3
                style={{
                  background: "linear-gradient(135deg, #0a3d62 0%, #1e5f8b 100%)",
                  WebkitBackgroundClip: "text",
                  WebkitTextFillColor: "transparent",
                  backgroundClip: "text",
                  marginBottom: "15px",
                  fontSize: "1.4rem",
                }}
              >
                🔄 Referrals
              </h3>
              <p>Submit and track patient referrals with real-time status updates.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

// Render the component if the container exists
const portalContainer = document.getElementById("portal-app")
if (portalContainer) {
  ReactDOM.render(<Portal />, portalContainer)
}
export default Portal;
