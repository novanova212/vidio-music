import type { Song } from '../types/media'

interface Props {
  song: Song
}

// Pemutar musik: tag <audio> native, sumber dari endpoint stream backend.
export default function AudioPlayer({ song }: Props) {
  return (
    <div className="player-card">
      {song.cover_url && (
        <img src={song.cover_url} alt={song.title} className="cover-art" />
      )}

      <audio controls preload="metadata" style={{ width: '100%' }}>
        <source src={song.stream_url} type={song.mime_type} />
        Browser Anda tidak mendukung tag audio.
      </audio>

      <div className="player-info">
        <h2>{song.title}</h2>
        {song.artist && <p>{song.artist}{song.album ? ` — ${song.album}` : ''}</p>}
        <a className="btn-download" href={song.download_url} download>
          Unduh musik (sumber asli)
        </a>
      </div>
    </div>
  )
}