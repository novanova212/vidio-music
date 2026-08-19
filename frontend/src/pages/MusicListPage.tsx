import { useEffect, useState } from 'react'
import { mediaApi } from '../api/client'
import type { Song } from '../types/media'
import MediaCard from '../components/MediaCard'

export default function MusicListPage() {
  const [songs, setSongs] = useState<Song[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    mediaApi.getSongs().then((res) => {
      setSongs(res.data)
      setLoading(false)
    })
  }, [])

  if (loading) return <p>Memuat daftar musik...</p>

  return (
    <div>
      <h1>Musik</h1>
      <div className="media-grid">
        {songs.map((s) => (
          <MediaCard
            key={s.id}
            to={`/music/${s.slug}`}
            title={s.title}
            subtitle={s.artist}
            thumbnail={s.cover_url}
            downloadUrl={s.download_url}
          />
        ))}
      </div>
    </div>
  )
}