import axios from 'axios'
import type { Paginated, Song, Video } from '../types/media'

// Base URL API Laravel.
// - Development: Vite mem-proxy '/api' ke backend lokal (lihat vite.config.ts),
//   jadi tanpa VITE_API_URL, path relatif '/api' sudah cukup.
// - Production (Vercel): frontend & backend beda domain, jadi WAJIB set
//   env VITE_API_URL di Vercel, contoh: https://nama-service.up.railway.app/api
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
})

export const mediaApi = {
  getVideos: (page = 1) =>
    api.get<Paginated<Video>>('/videos', { params: { page } }).then((r) => r.data),

  getVideo: (slug: string) =>
    api.get<Video>(`/videos/${slug}`).then((r) => r.data),

  getSongs: (page = 1) =>
    api.get<Paginated<Song>>('/songs', { params: { page } }).then((r) => r.data),

  getSong: (slug: string) =>
    api.get<Song>(`/songs/${slug}`).then((r) => r.data),
}

export default api

// Upload video baru (multipart/form-data)
export function uploadVideo(data: { title: string; description?: string; file: File; thumbnail?: File }) {
  const form = new FormData()
  form.append('title', data.title)
  if (data.description) form.append('description', data.description)
  form.append('file', data.file)
  if (data.thumbnail) form.append('thumbnail', data.thumbnail)
  return api.post('/videos', form, { headers: { 'Content-Type': 'multipart/form-data' } })
}

// Upload musik baru (multipart/form-data)
export function uploadSong(data: { title: string; artist?: string; album?: string; file: File; cover?: File }) {
  const form = new FormData()
  form.append('title', data.title)
  if (data.artist) form.append('artist', data.artist)
  if (data.album) form.append('album', data.album)
  form.append('file', data.file)
  if (data.cover) form.append('cover', data.cover)
  return api.post('/songs', form, { headers: { 'Content-Type': 'multipart/form-data' } })
}