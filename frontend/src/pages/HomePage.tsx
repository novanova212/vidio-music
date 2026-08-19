import { Link } from 'react-router-dom'

// Halaman beranda: pintasan ke daftar video dan daftar musik.
export default function HomePage() {
  return (
    <div className="home-hero">
      <h1>Vidio Music</h1>
      <p>Putar dan unduh video & musik langsung dari sumber aslinya.</p>
      <div className="home-links">
        <Link to="/videos" className="btn-primary">Jelajahi Video</Link>
        <Link to="/music" className="btn-primary">Jelajahi Musik</Link>
      </div>
    </div>
  )
}