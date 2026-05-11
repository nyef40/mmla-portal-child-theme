"use client";
import { useState, useEffect } from 'react';
import api from '../utils/api';
import PortalNavigation from '../components/PortalNavigation';
import ResourceCard from '../components/ResourceCard';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

const Resources = () => {
    const [resources, setResources] = useState([
        {
            id: 1,
            title: 'Understanding HIPAA Compliance',
            excerpt: 'Guide to HIPAA compliance',
            fileUrl: '/wp-content/uploads/HIPAA_BAA.pdf',
            access_level: 'all'
        }
    ]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchResources = async () => {
            try {
                setLoading(true);
                const response = await api.getResources();
                if (response.success) {
                    setResources(response.data);
                    setError(null);
                } else {
                    setError(response.message || 'Failed to load resources');
                }
            } catch (err) {
                setError('Failed to load resources: ' + err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchResources();
    }, []);

    if (loading) return <LoadingSpinner />;
    if (error) return <ErrorMessage message={error} />;

    return (
        <div className="portal-resources">
            <div className="portal-layout">
                <PortalNavigation activePage="resources" />
                <div className="portal-content">
                    <h1>Resources</h1>
                    <p>Access training materials and documents.</p>
                    <p>Last resource accessed: Understanding HIPAA Compliance</p>
                    <div className="resources-list">
                        {resources.length === 0 ? (
                            <p>No resources available.</p>
                        ) : (
                            resources.map(resource => (
                                <ResourceCard key={resource.id} resource={resource} />
                            ))
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Resources;
