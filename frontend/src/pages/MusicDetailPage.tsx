import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { mediaApi } from '../api/client'
import type { Song } from '../types/media'
import AudioPlayer from '../components/AudioPlayer'

export default function MusicDetailPage() {
  const { slug } = useParams<{ slug: string }>()
  const [song, setSong] = useState<Song | null>(null)

  useEffect(() => {
    if (slug) mediaApi.getSong(slug).then(setSong)
  }, [slug])

  if (!song) return <p>Memuat musik...</p>

  return <AudioPlayer song={song} />
}