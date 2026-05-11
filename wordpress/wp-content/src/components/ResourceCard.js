import React from 'react';

const ResourceCard = ({ resource, onClick }) => {
  // Color schemes based on category
  const getGradient = (category) => {
    const gradients = {
      'Compliance': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      'Clinical': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
      'Safety': 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)',
      'Education': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      'default': 'linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%)'
    };
    return gradients[category] || gradients['default'];
  };

  const getIcon = (type) => {
    const icons = {
      'PDF': '📄',
      'Video': '🎬',
      'Link': '🔗',
      'Document': '📋'
    };
    return icons[type] || '📁';
  };

  return (
    <div 
      className="resource-card"
      onClick={onClick}
      style={{
        background: '#ffffff',
        borderRadius: '16px',
        overflow: 'hidden',
        boxShadow: '0 4px 20px rgba(0, 0, 0, 0.08)',
        transition: 'all 0.3s ease',
        cursor: 'pointer',
        position: 'relative'
      }}
      onMouseOver={(e) => {
        e.currentTarget.style.transform = 'translateY(-4px)';
        e.currentTarget.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.15)';
      }}
      onMouseOut={(e) => {
        e.currentTarget.style.transform = 'translateY(0)';
        e.currentTarget.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08)';
      }}
    >
      {/* Gradient Header */}
      <div 
        style={{
          background: getGradient(resource.category),
          padding: '24px 20px',
          position: 'relative'
        }}
      >
        <span 
          style={{
            position: 'absolute',
            top: '12px',
            right: '12px',
            background: 'rgba(255,255,255,0.25)',
            color: 'white',
            padding: '4px 12px',
            borderRadius: '20px',
            fontSize: '12px',
            fontWeight: '600',
            backdropFilter: 'blur(4px)'
          }}
        >
          {resource.type}
        </span>
        <div style={{ fontSize: '40px', marginBottom: '8px' }}>
          {getIcon(resource.type)}
        </div>
        <h3 style={{
          color: 'white',
          margin: 0,
          fontSize: '18px',
          fontWeight: '700',
          lineHeight: '1.3'
        }}>
          {resource.title}
        </h3>
      </div>
      
      {/* Card Body */}
      <div style={{ padding: '20px' }}>
        <p style={{
          color: '#64748b',
          fontSize: '14px',
          lineHeight: '1.6',
          margin: '0 0 16px 0'
        }}>
          {resource.description}
        </p>
        
        <div style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}>
          <span style={{
            background: 'linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%)',
            color: '#475569',
            padding: '6px 14px',
            borderRadius: '20px',
            fontSize: '12px',
            fontWeight: '600'
          }}>
            {resource.category}
          </span>
          
          <span style={{
            color: '#0A3D62',
            fontWeight: '600',
            fontSize: '14px',
            display: 'flex',
            alignItems: 'center',
            gap: '4px'
          }}>
            View →
          </span>
        </div>
      </div>
    </div>
  );
};

export default ResourceCard;
