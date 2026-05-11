"use client"

import { useEffect, useState } from "react"
import LoadingSpinner from "../components/LoadingSpinner"

function IVIGNews() {
  const [posts, setPosts] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Fetch WordPress posts from IVIG News category
    fetch("/wp-json/wp/v2/posts?categories=IVIG_CATEGORY_ID&per_page=10")
      .then((res) => res.json())
      .then((data) => {
        setPosts(data)
        setLoading(false)
      })
      .catch(() => {
        // Fallback to sample posts
        setPosts(getSamplePosts())
        setLoading(false)
      })
  }, [])

  if (loading) return <LoadingSpinner />

  return (
    <div className="page-ivig-news">
      <div className="hero-section">
        <h1>IVIG News & Updates</h1>
        <p>Latest developments in immunoglobulin therapy</p>
      </div>

      <div className="news-content">
        {posts.length > 0 ? (
          <div className="posts-grid">
            {posts.map((post) => (
              <NewsCard
                key={post.id}
                title={post.title?.rendered || post.title}
                excerpt={post.excerpt?.rendered || post.excerpt}
                date={post.date}
                link={post.link}
              />
            ))}
          </div>
        ) : (
          <p>No news articles available at this time.</p>
        )}
      </div>
    </div>
  )
}

function NewsCard({ title, excerpt, date, link }) {
  return (
    <article className="news-card">
      <h3>{title}</h3>
      <div className="news-meta">
        <span className="news-date">{new Date(date).toLocaleDateString()}</span>
      </div>
      <div className="news-excerpt" dangerouslySetInnerHTML={{ __html: excerpt }} />
      <a href={link} className="read-more">
        Read More →
      </a>
    </article>
  )
}

function getSamplePosts() {
  return [
    {
      id: 1,
      title: "Latest IVIG Research Findings",
      excerpt: "<p>Recent studies show promising results...</p>",
      date: new Date().toISOString(),
      link: "#",
    },
  ]
}

export default IVIGNews
