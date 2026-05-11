import React, { useState, useEffect } from 'react';
import { post } from '../utils/api';
import LoadingSpinner from './LoadingSpinner';

function Resources() {
  const [resources, setResources] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    loadResources();
  }, []);

  const loadResources = async () => {
    try {
      const response = await post('get_resources');
      console.log('[Portal]', response);
      if (response.success) {
        setResources(response.data || []);
      }
    } catch (err) {
      console.error('Failed to load resources:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingSpinner message="Loading resources..." />;

  const categories = ['all', ...new Set(resources.map((r) => r.category).filter(Boolean))];
  const filtered = filter === 'all' ? resources : resources.filter((r) => r.category === filter);

  const getCategoryGradient = (cat) => {
    const gradients = {
      Compliance: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      Clinical: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
      Safety: 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)',
      Education: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      General: 'linear-gradient(135deg, #0A3D62 0%, #2980b9 100%)',
      all: 'linear-gradient(135deg, #0A3D62 0%, #2980b9 100%)',
    };
    return gradients[cat] || gradients.General;
  };

  return (
    <div className="portal-page resources-page">
      <div className="portal-container">
        <h1 className="page-title">Resources</h1>
        <p className="page-subtitle">Access educational materials and important documents</p>

        <div className="filter-tabs">
          {categories.map((cat) => (
            <button
              key={cat}
              className={`filter-tab ${filter === cat ? 'active' : ''}`}
              onClick={() => setFilter(cat)}
              style={filter === cat ? { background: getCategoryGradient(cat) } : {}}
            >
              {cat === 'all' ? 'All Resources' : cat}
            </button>
          ))}
        </div>

        {filtered.length === 0 ? (
          <div className="empty-state">
            <p>No resources available at this time.</p>
          </div>
        ) : (
          <div className="resources-grid">
            {filtered.map((resource) => (
              <div key={resource.id} className="resource-card">
                <div className="resource-header" style={{ background: getCategoryGradient(resource.category) }}>
                  <span className="resource-type">{resource.type}</span>
                  <h3>{resource.title}</h3>
                </div>
                <div className="resource-body">
                  <p>{resource.description}</p>
                  <div className="resource-footer">
                    <span className="resource-category">{resource.category}</span>
                    <a
                      href={resource.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="btn btn-sm"
                    >
                      View
                    </a>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export default Resources;
