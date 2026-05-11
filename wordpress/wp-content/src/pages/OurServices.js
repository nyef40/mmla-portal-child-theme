"use client"

import { useEffect, useState } from "react"
import LoadingSpinner from "../components/LoadingSpinner"

function OurServices() {
  const [content, setContent] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Fetch WordPress content via REST API or load static content
    fetch("/wp-json/wp/v2/pages?slug=our-services")
      .then((res) => res.json())
      .then((data) => {
        if (data[0]) {
          setContent(data[0].content.rendered)
        }
        setLoading(false)
      })
      .catch(() => {
        // Fallback to static content
        setContent(getStaticServicesContent())
        setLoading(false)
      })
  }, [])

  if (loading) return <LoadingSpinner />

  return (
    <div className="page-our-services">
      <div className="hero-section">
        <h1>Our Services</h1>
        <p>Comprehensive Home Health Care Solutions</p>
      </div>

      <div className="services-content">
        {content ? (
          <div dangerouslySetInnerHTML={{ __html: content }} />
        ) : (
          <div className="services-grid">
            <ServiceCard
              icon="💉"
              title="IVIG Therapy"
              description="Expert intravenous immunoglobulin therapy administered in the comfort of your home."
            />
            <ServiceCard
              icon="🏥"
              title="Home Health Care"
              description="Professional nursing care and medical services delivered to your doorstep."
            />
            <ServiceCard
              icon="🩺"
              title="Patient Monitoring"
              description="Continuous monitoring and care coordination for optimal health outcomes."
            />
            <ServiceCard
              icon="📋"
              title="Care Coordination"
              description="Seamless coordination between healthcare providers and family members."
            />
          </div>
        )}
      </div>
    </div>
  )
}

function ServiceCard({ icon, title, description }) {
  return (
    <div className="service-card">
      <div className="service-icon">{icon}</div>
      <h3>{title}</h3>
      <p>{description}</p>
    </div>
  )
}

function getStaticServicesContent() {
  return `
    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon">💉</div>
        <h3>IVIG Therapy</h3>
        <p>Expert intravenous immunoglobulin therapy administered in the comfort of your home.</p>
      </div>
    </div>
  `
}

export default OurServices
