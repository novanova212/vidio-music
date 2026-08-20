import { useState, FormEvent } from 'react'
import { uploadSong, uploadVideo } from '../api/client'

// Halaman upload sederhana: pilih tipe (video/musik), isi judul, pilih file,
// lalu kirim ke backend. Setelah sukses, file langsung muncul di daftar
// Video/Musik (karena backend menyimpan & mengembalikannya lewat GET /api/...).
export default function UploadPage() {
  const [type, setType] = useState<'video' | 'song'>('video')
  const [title, setTitle] = useState('')
  const [extra, setExtra] = useState('') // description (video) atau artist (musik)
  const [file, setFile] = useState<File | null>(null)
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle')
  const [errorMsg, setErrorMsg] = useState('')

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    if (!file) return
    setStatus('loading')
    setErrorMsg('')

    try {
      if (type === 'video') {
        await uploadVideo({ title, description: extra, file })
      } else {
        await uploadSong({ title, artist: extra, file })
      }
      setStatus('success')
      setTitle('')
      setExtra('')
      setFile(null)
    } catch (err: any) {
      setStatus('error')
      setErrorMsg(err?.response?.data?.message || 'Upload gagal. Cek ukuran/format file.')
    }
  }

  return (
    <div className="player-card">
      <h1>Upload {type === 'video' ? 'Video' : 'Musik'}</h1>

      <div className="upload-type-switch">
        <button
          type="button"
          className={type === 'video' ? 'btn-primary' : 'btn-download small'}
          onClick={() => setType('video')}
        >
          Video
        </button>
        <button
          type="button"
          className={type === 'song' ? 'btn-primary' : 'btn-download small'}
          onClick={() => setType('song')}
        >
          Musik
        </button>
      </div>

      <form onSubmit={handleSubmit} className="upload-form">
        <label>
          Judul
          <input value={title} onChange={(e) => setTitle(e.target.value)} required />
        </label>

        <label>
          {type === 'video' ? 'Deskripsi (opsional)' : 'Artis (opsional)'}
          <input value={extra} onChange={(e) => setExtra(e.target.value)} />
        </label>

        <label>
          File {type === 'video' ? 'video (.mp4/.webm/.mov)' : 'musik (.mp3/.wav/.flac)'}
          <input
            type="file"
            accept={type === 'video' ? 'video/*' : 'audio/*'}
            onChange={(e) => setFile(e.target.files?.[0] ?? null)}
            required
          />
        </label>

        <button type="submit" className="btn-primary" disabled={status === 'loading'}>
          {status === 'loading' ? 'Mengunggah...' : 'Upload'}
        </button>

        {status === 'success' && <p className="upload-success">Berhasil diunggah!</p>}
        {status === 'error' && <p className="upload-error">{errorMsg}</p>}
      </form>
    </div>
  )
}