import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { mediaApi } from '../api/client'
import type { Video } from '../types/media'
import VideoPlayer from '../components/VideoPlayer'

export default function VideoDetailPage() {
  const { slug } = useParams<{ slug: string }>()
  const [video, setVideo] = useState<Video | null>(null)

  useEffect(() => {
    if (slug) mediaApi.getVideo(slug).then(setVideo)
  }, [slug])

  if (!video) return <p>Memuat video...</p>

  return <VideoPlayer video={video} />
}