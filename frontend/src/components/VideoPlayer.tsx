import type { Video } from '../types/media'

interface Props {
  video: Video
}

// Pemutar video: memakai tag <video> native browser, sumber langsung
// dari endpoint stream backend (mendukung seek via HTTP Range).
export default function VideoPlayer({ video }: Props) {
  return (
    <div className="player-card">
      <video
        controls
        preload="metadata"
        poster={video.thumbnail_url ?? undefined}
        style={{ width: '100%', borderRadius: 8, background: '#000' }}
      >
        <source src={video.stream_url} type={video.mime_type} />
        Browser Anda tidak mendukung tag video.
      </video>

      <div className="player-info">
        <h2>{video.title}</h2>
        {video.description && <p>{video.description}</p>}
        <a className="btn-download" href={video.download_url} download>
          Unduh video (sumber asli)
        </a>
      </div>
    </div>
  )
}