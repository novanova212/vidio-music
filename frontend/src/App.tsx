import { Link, Route, Routes } from 'react-router-dom'
import HomePage from './pages/HomePage'
import VideoListPage from './pages/VideoListPage'
import VideoDetailPage from './pages/VideoDetailPage'
import MusicListPage from './pages/MusicListPage'
import MusicDetailPage from './pages/MusicDetailPage'
import UploadPage from './pages/UploadPage'
import './styles/index.css'

export default function App() {
  return (
    <div className="app-shell">
      <nav className="navbar">
        <Link to="/" className="brand">Vidio Music</Link>
        <div className="nav-links">
          <Link to="/videos">Video</Link>
          <Link to="/music">Musik</Link>
          <Link to="/upload">Upload</Link>
        </div>
      </nav>

      <main className="content">
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/videos" element={<VideoListPage />} />
          <Route path="/videos/:slug" element={<VideoDetailPage />} />
          <Route path="/music" element={<MusicListPage />} />
          <Route path="/music/:slug" element={<MusicDetailPage />} />
          <Route path="/upload" element={<UploadPage />} />
        </Routes>
      </main>
    </div>
  )
}