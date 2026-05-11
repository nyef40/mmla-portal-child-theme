import React, { useState, useEffect } from 'react';

const Resources = () => {
  const [resources, setResources] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    fetchResources();
  }, []);

  const fetchResources = async () => {
    try {
      const formData = new FormData();
      formData.append('action', 'get_resources');

      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();
      if (data.success) {
        setResources(data.data);
      }
    } catch (err) {
      console.error('Failed to fetch resources:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleResourceClick = async (resource) => {
    const formData = new FormData();
    formData.append('action', 'log_resource_access');
    formData.append('resource_id', resource.id);

    await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    });

    window.open(resource.url, '_blank');
  };

  const categories = ['all', ...new Set(resources.map(r => r.category))];
  const filteredResources = filter === 'all' 
    ? resources 
    : resources.filter(r => r.category === filter);

  if (loading) return <div className="portal-loading">Loading resources...</div>;

  return (
    <div className="portal-page resources-page">
      <div className="portal-container">
        <h1>Resources</h1>
        <p>Access educational materials and important documents.</p>
        
        <div className="filter-tabs">
          {categories.map(cat => (
            <button
              key={cat}
              className={`filter-tab ${filter === cat ? 'active' : ''}`}
              onClick={() => setFilter(cat)}
            >
              {cat.charAt(0).toUpperCase() + cat.slice(1)}
            </button>
          ))}
        </div>
        
        <div className="resources-grid">
          {filteredResources.map(resource => (
            <div key={resource.id} className="portal-card resource-card">
              <span className="resource-type">{resource.type}</span>
              <h3>{resource.title}</h3>
              <p>{resource.description}</p>
              <span className="resource-category">{resource.category}</span>
              <button 
                className="portal-btn"
                onClick={() => handleResourceClick(resource)}
              >
                View Resource
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Resources;